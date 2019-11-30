<?php
namespace Farol\Classes\Json;

use Illuminate\Support\Collection;

class Items{

	protected $collection;

	public function __construct(){
		$this->collection = new Collection();
	}

	public function append( $obj, array $args=[] ) : Items{

		$this->collection->push(["object"=>$obj, "args"=>$args]);

		return $this;

	}

	public function collection() : Collection{
		return $this->collection;
	}

	public function set( $obj, array $args=[] ) : Items{

		$this->collection = new Collection();
		$this->collection->push(["object"=>$obj, "args"=>$args]);

		return $this;

	}

}
