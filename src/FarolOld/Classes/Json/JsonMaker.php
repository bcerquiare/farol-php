<?php
namespace Farol\Classes\Json;

use Closure;
use Illuminate\Contracts\Support\Jsonable;

class JsonMaker implements Jsonable{

	public $data;
	public $meta;
	public $links;
	public $success = true;
	public $error = false;
	public $message = false;

	public function __construct(){
		$this->data = new Data();
	}

	/**
	 * Retorna a instancia data
	 * @return Data
	 */
	public function data(){
		return $this->data;
	}

	public function success( $message="" ){

		$this->success = true;
		$this->error = false;
		$this->message = $message;

		return $this;

	}

	public function error( $message="" ){

		$this->error = true;
		$this->success = false;
		$this->message = $message;

		return $this;

	}

    public function configure(Closure $fn):JsonMaker{
        $fn($this);
        return $this;
    }

	public function toArray(){
		return ( new JsonExporter($this) )->toArray();
	}

	public function toJson($options = 0){
		return \json_encode( $this->toArray() );
	}

}
