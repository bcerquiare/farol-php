<?php
namespace Farol\Classes\Json;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class JsonExporter implements Jsonable{

	protected $maker;

	public function __construct( JsonMaker $maker ){
		$this->maker = $maker;
	}

	public function toArray(){

		$arr = [
			"success" 	=> $this->maker->success,
			"error" 	=> $this->maker->error,
			"message" 	=> $this->maker->message,
			"data" => [
				"model" 	=> [],
				"structure" => [],
				"items" 	=> [],
			],
			"meta" 	=> [],
			"links" => [],
		];

		$this->fetchDataModel($arr);
		$this->fetchDataItems($arr);
		$this->fetchDataStructure($arr);

		return $arr;

	}

	public function fetchDataModel( array &$arr ){

		$this->maker->data()->model()->collection()->each(function( $item, $index ) use ( &$arr ){

            $data = $this->fetchObject( $item["object"], $item["args"]);
			$arr["data"]["model"] = \array_merge( $arr["data"]["model"], ( $data ? $data : [] ) );

		});

	}

	public function fetchDataStructure( array &$arr ){

		$this->maker->data()->structure()->collection()->each(function( $item, $index ) use ( &$arr ){

            $data = $this->fetchObject( $item["object"], $item["args"]);
			$arr["data"]["structure"] = \array_merge( $arr["data"]["structure"], ( $data ? $data : [] ) );

		});

	}

	public function fetchDataItems( array &$arr ){

		$collection = $this->maker->data()->items()->collection();

        if( !$collection->count() ){
            return;
        }

		// se tiver apenas um item e for paginator
		if( $collection->count() == 1 && $collection->get(0)["object"] instanceof LengthAwarePaginator ){

			$arr["data"]["items"] = $this->fetchPaginator(
				$arr,
				$collection->get(0)["object"],
				$collection->get(0)["args"]
			);

		}else{

             $arr["data"]["items"] = [];

			$this->maker->data()->items()->collection()->each(function( $item, $index ) use ( &$arr ){

                $arr["data"]["items"] = \array_merge( $arr["data"]["items"], $this->fetchObject( $item["object"], $item["args"] ) );

			});

		}

	}

	public function fetchObject( $object, $args ){

		if( $object instanceof Model ){

			$data = [];
			$this->fetchModel($data, $object, $args);

			return $data;

		}elseif( $object instanceof Collection ){

            return $object->toArray();

		}elseif( $object instanceof \Illuminate\Database\Eloquent\Builder ){

            return $object->get()->toArray();

        }else{
            return $object;
        }

	}

	public function fetchModel( array &$arr, Model $model, $args=[] ){

		$arr = $model->toArray();
		$this->fetchModelRelationships( $arr, $model, ( isset( $args["with"] ) ? $args["with"] : [] ) );

	}

	public function fetchArrayModel( array &$arr, RelationType $relationType, Collection $collection ){

        $collection->each(function( $item ) use( &$arr, $relationType ){
            $arr[] = $item->getKey();
        });

	}

	protected function fetchModelRelationships( &$arr, Model $model, $arrWith ){

		foreach( $arrWith as $k => $v ){

			// Existem relacionamentos dentro
			if( \is_array($v) ){
				$this->fetchModelRelationship( $arr, $model, $k, $v );
			}else{

				// Não existem relacionamentos dentro
				$this->fetchModelRelationship( $arr, $model, $v, [] );

			}

		}

	}

	protected function fetchModelRelationship( &$arr, Model $model, $relationString, $arrWith ){

		$relation = new RelationType($relationString);
		$relationName = $relation->relationName();
		$relationObject	= $model->$relationName();

		if( $relationObject instanceof HasOne || $relationObject instanceof BelongsTo){

			$relationData = $model->$relationName;

			if( $relation->resultType() == RelationType::RESULT_AS_APPEND ){

				$arr = \array_merge( $arr, $relationData->toArray() );
				$this->fetchModelRelationships( $arr, $relationData, $arrWith );

			}else if( $relation->resultType() == RelationType::RESULT_AS_MODEL){

				if( $relationData ){
					$arr[$relationName] = $relationData->toArray();
					$this->fetchModelRelationships( $arr[$relationName], $relationData, $arrWith );
				}else{
					$arr[$relationName] = (object)[];
				}

			}

		}else if(
			$relationObject instanceof HasManyThrough ||
			$relationObject instanceof HasMany ||
			$relationObject instanceof BelongsToMany
		){

			$relationData = $model->$relationName;

			$arr[$relationName] = [];

			$relationData->each(function(Model $model) use (&$arr, $relation, $relationName, $relationObject, $relationData, $arrWith){

				$item = $model->toArray();
				$this->fetchModelRelationships( $item, $model, $arrWith );

				$arr[$relationName][] = $item;

			});

		}else if( $relationObject instanceof Builder ){

			if( $relation->resultType() == RelationType::RESULT_AS_APPEND ){
				$arr = \array_merge( $arr, $relationObject->get()->toArray() );
			}else if( $relation->resultType() == RelationType::RESULT_AS_MODEL){
				$arr[$relationName] = $relationObject->get();
			}

		}

	}

	/**
	 * Escolhe o tipo de exportação dos dados, array ou objeto
	 *
	 * @param [type] $relation
	 * @param [type] $relationData
	 * @return void
	 */
	protected function exportObjectByRelation( $relation, $relationData ){

		if( $relation instanceof HasOne ){
			return ( $relationData ? $relationData->toArray() : (object)[] );
		}else if( $relation instanceof HasMany || $relation instanceof HasManyThrough ){
			return ( $relationData ? $relationData->toArray() : [] );
		}else{
			return ( $relationData ? $relationData->toArray() : [] );
		}

	}

    public function fetchRelationType(){

    }

	public function fetchPaginator( array &$arr, LengthAwarePaginator $paginator, $args ){

		// adiciono os dados a meta
		$arr["meta"]["current_page"] 	= $paginator->currentPage();
		$arr["meta"]["per_page"] 		= $paginator->perPage();
		$arr["meta"]["total"] 			= $paginator->total();
		$arr["meta"]["last_page"] 		= $paginator->lastPage();

		// fetch do object
		$items = [];

		foreach( $paginator->items() as $k => $object ){
			$items[] = $this->fetchObject( $object, $args );
		}

		return $items;

	}

	public function toJson($options = 0) : string{
		return json_encode( $this->toArray() );
	}

}
