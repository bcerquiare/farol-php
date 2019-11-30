<?php
namespace Farol\Classes\Json;

class Data{

	protected $structure;
	protected $model;
	protected $items;

	public function __construct(){
		$this->structure 	= new Structure();
		$this->model 		= new Model();
		$this->items 		= new Items();
	}

	public function model() : Model{
		return $this->model;
	}

	public function structure() : Structure{
		return $this->structure;
	}

	public function items() : Items{
		return $this->items;
	}

}
