<?php
namespace Farol\Classes\Json;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Model;
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

			if( \is_array($v) ){

				$relationName = $k;
				$relationData = $model->$relationName;
				$relationWith = $v;

				$relationData->each(function( $item ) use ( &$arr, $relationName, $relationWith ){

					$data = [];
					$this->fetchModel( $data, $item, ["with"=>$relationWith] );

					$arr[ $relationName ][] = $data;

				});

			}else{

                $relation = new RelationType($v);
				$relationName = $relation->relationName();
				$relationData = $model->$relationName;

				if( $relationData instanceof Collection ){

                    if( $relation->resultType() == RelationType::RESULT_AS_MODEL ){

                        $relationData->each(function( $item, $index ) use ( &$arr, $relation ){

                            $arr[ $relation->relationName() ][$index] = [];
                            $this->fetchModel( $arr[ $relation->relationName() ][$index], $item, ["with"=>[]] );

                        });

                    }else if( $relation->resultType() == RelationType::RESULT_AS_ARRAY ){

                        $arr[ $relation->relationNameAlias() ] = [];
                        $this->fetchArrayModel( $arr[ $relation->relationNameAlias() ], $relation, $relationData );

                    }else if( $relation->resultType() == RelationType::RESULT_AS_APPEND ){

						dd("FUNCAO NAO IMPLAMENTADA EM JSONEXPORTER");
					}

				}else{

					if( $relation->resultType() == RelationType::RESULT_AS_APPEND ){
						$arr = \array_merge( $arr, ( $relationData ? $relationData->toArray() : [] ) );
					}else{
						$arr[ $relationName ] = $relationData;
					}

				}

			}

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
