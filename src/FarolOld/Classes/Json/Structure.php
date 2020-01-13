<?php
namespace Farol\Classes\Json;

use Illuminate\Support\Collection;

class Structure{

	protected $collection;

	public function __construct(){
		$this->collection = new Collection();
	}

	public function append( $obj, array $args=[] ) : Structure{

		$this->collection->push(["object"=>$obj, "args"=>$args]);

		return $this;

	}

	public function collection() : Collection{
		return $this->collection;
	}

}
