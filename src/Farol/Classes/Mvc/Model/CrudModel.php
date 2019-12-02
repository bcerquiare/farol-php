<?php

namespace Farol\Classes\Mvc\Model;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as IlluminateValidator;

class CrudModel{

    protected $model;

    public function __construct( EloquentModel $model ){
        $this->model = $model;
    }

	/**
	 * Função executada antes de inserir no banco
	 * Permite a manipulação da array
	 * @param array $data
	 * @return void
	 */
	public function beforeStore(array &$data){
	}

	/**
	 * Executa a validação dos dados antes da inserção
	 *
	 * @param array $data
	 * @return void
	 */
	public function validateStore(array $data) : ?IlluminateValidator{
		return null;
	}

    /**
     * Insere um novo registro da model
     * @param array $data
     * @return EloquentModel
     */
    public function store(array $data) : EloquentModel{

        if( $this->model->getKey() ){
            throw new \Exception("O id da model já está atribuida. Tente usar update ao invés de create.");
        }

        $columns = $this->model->getFillable();
        $fill = [];

        foreach($columns as $column){
            $fill[ $column ] = $data[ $column ];
        }

		$this->beforeStore($data);
		$validator = $this->validateStore($data);

		if( $validator && $validator->fails() ){
			throw new \Exception("Erro de validação " . \implode(", ", $validator->getMessageBag()->getMessages()));
		}

        $this->model->fill($fill);
        $this->model->save();

        $this->afterStore($this->model, $data);
        $this->afterSave($this->model, $data);

        return $this->model;

    }

    /**
     * Função chamada depois que a model é salva no banco
     * @param EloquentModel $model
     * @param array $data
     * @return void
     */
    public function afterStore($model, array $data){
    }

	/**
	 * Função chamada antes de executar um update
	 * Permite a manipulação da array
	 * @param array $data
	 * @return void
	 */
	public function beforeUpdate(array &$data){
	}

	/**
	 * Executa a validação dos dados antes do update
	 * @param array $data
	 * @return void
	 */
	public function validateUpdate(array $data) : ?IlluminateValidator{
		return null;
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

		$this->beforeUpdate($data);
		$validator = $this->validateUpdate($data);

		if( $validator && $validator->fails() ){
			throw new \Exception("Erro de validação " . \implode(", ", $validator->getMessageBag()->getMessages()));
		}

        $this->model->fill($fill);
        $this->model->save();

        $this->afterUpdate($this->model, $data);
        $this->afterSave($this->model, $data);

        return $this->model;

    }

    /**
     * Função chamada depois que a model é atualizada
     * @param EloquentModel $model
     * @param array $data
     * @return void
     */
    public function afterUpdate($model, array $data){

    }

	/**
	 * Função chamada depois de um store ou update
	 * @param EloquentModel $model
	 * @param array $data
	 * @return void
	 */
	public function afterSave($model, array $data){

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
