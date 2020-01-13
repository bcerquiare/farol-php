<?php

namespace Farol\Classes\Mvc\Model;

use Illuminate\Database\Eloquent\Relations\Pivot as IlluminatePivot;

class Pivot extends IlluminatePivot{
	
    protected $table        = 'table_name';
    protected $primaryKey   = 'primary_key_name';
    public $timestamps      = false;
    protected $dateFormat   = 'U';
	public $incrementing = true;

}
