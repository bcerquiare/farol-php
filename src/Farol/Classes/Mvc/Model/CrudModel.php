<?php

namespace Farol\Classes\Mvc\Model;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class CrudModel{

    protected $model;

    public function __construct( EloquentModel $model ){
        $this->model = $model;
    }

    /**
     * Insere um novo registro da model
     * @param array $data
     * @return EloquentModel
     */
    public function create(array $data) : EloquentModel{

        if( $this->model->getKey() ){
            throw new \Exception("O id da model já está atribuida. Tente usar update ao invés de create.");
        }

        $columns = $this->model->getFillable();
        $fill = [];

        foreach($columns as $column){

            $fill[ $column ] = $data[ $column ];

        }

        $this->model->fill($fill);
        $this->model->save();

        $this->afterCreate($data);

        return $this->model;

    }

    /**
     * Função chamada depois que a model é salva no banco
     * @param array $data
     * @return void
     */
    public function afterCreate(array $data){
    }

    /**
     * Atualiza dados da model
     * @param array $data
     * @return EloquentModel
     */
    public function update(array $data) : EloquentModel{

        if( !$this->model->getKey() ){
            throw new \Exception("O id da model não está atribuida.");
        }

        $columns = $this->model->getFillable();
        $fill = [];

        foreach($columns as $column){
            $fill[ $column ] = $data[ $column ];
        }

        $this->model->fill($fill);
        $this->model->save();

        $this->afterUpdate($data);

        return $this->model;

    }

    /**
     * Função chamada depois que a model é atualizada
     * @param array $data
     * @return void
     */
    public function afterUpdate(array $data){

    }

    /**
     * Undocumented function
     * @return void
     */
    public function remove(){

        if( !$this->model->getKey() ){
            throw new \Exception("O id da model não está atribuida.");
        }

        $this->model->delete();
        $this->afterRemove();

    }

    public function afterRemove(){

    }

}
