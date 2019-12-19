<?php

namespace Farol\Traits\Mvc\Controller;

use Farol\Classes\Json\JsonMaker;
use Farol\Classes\Mvc\Model as FarolModel;
use Illuminate\Database\Eloquent\Model;

trait CrudController{

    abstract public function model() : string;

    /**
     * Retorna uma instancia do model.
     * Qaundo passado $id retorna o model relacionado
     * Quando não passado retorna a instancia vazia da classe
     *
     * @param int $id
     * @return FarolModel|null
     */
    public function getModel($id=null) : ? FarolModel {

		if( is_null($id) ){

			$string = $this->model();
			return new $string();

		}else{

			$string = $this->model();
			return call_user_func_array( "\\" . $string .'::find', [$id] );

		}

    }

    /**
     * Modela o retorno da model para o json
     * @param Model $model
     * @return JsonMaker
     */
    public function resource(Model $model) : JsonMaker{

        return json()->configure(function(JsonMaker $json) use ($model){
            $json->data()->model()->append($model);
        });

    }

	/**
	 * Retorna a view de indice
	 * @return JsonMaker|array
	 */
    public function index(){

        return json()->configure(function(JsonMaker $json){
			$json->data()->items()->set( $this->indexList() );
		});

    }

	protected function indexList(){
		return $this->getModel()->select();
	}

    /**
     * Retorno da função view usado para ver apenas os dados da model
     * @param int|Model $param
     * @return JsonMaker
     */
    public function view($param) : JsonMaker{
        $model = $this->getviewModelReturn($param);
        return $this->resource($model);
    }

    /**
     * Alias de view mas usando o id como numerico
     * @param int $id
     * @return JsonMaker|array
     */
    public function show($id){
        return $this->view($id);
    }

    /**
     * Trata o model que sera retornado ou passando como parametro o objeto ou o id
     * @param int|Model $param
     * @return Model
     */
    protected function getViewModelReturn( $param ) : Model{

        if( $param instanceof Model ){
            return $param;
        }else if( \is_integer( $param ) || \is_string( $param ) ){
            return $this->getModel($param);
        }else{
            return null;
        }

    }

    /**
     * Retorna os dados de estrutura que serão usados nos formulários
     * @return array
     */
    public function structure( array $data=[] ){
        return [];
    }

    /**
     * Retorna a estrutura para a função create
     * @return JsonMaker|array
     */
    public function create(){

        return json()->configure(function( JsonMaker $json ){
            $json->data()->structure()->append( $this->structure() );
        });

    }

    /**
     * Função chamada pelo rota para salvar um model e retorna a estrutura de edit caso tenha sucesso
     * @return JsonMaker
     */
    public function store(){

        $data = request()->all();
        $model = $this->storeModel( $data );

        return $this->edit( $model );

    }

    /**
     * Cria a model no banco de dados e retorna sua instancia
     * @param array $data
     * @return FarolModel
     */
    protected function storeModel(array $data) : FarolModel{

        $model  = $this->getModel();
        $crud   = $model->crud();

        $crud->store($data);

        return $model;

    }

    /**
     * Retorna a estrutura para a função edit
     * @param int $id
     * @return JsonMaker|array
     */
    public function edit($id){

		$resource = $this->view($id);
		//$data = $resource->toArray()["data"]["model"];

        return $resource->configure(function( JsonMaker $json ) use ( $data ){
            $json->data()->structure()->append( $this->structure( $data ) );
        });

    }

    /**
     * Faz o update da model
     * @param int $id
     * @return JsonMaker|array
     */
    public function update($id){

        $data 	= request()->all();
        $model  = $this->getModel($id);
        $crud   = $model->crud();

        $crud->update($data);

        return $this->edit( $model );

    }

}
