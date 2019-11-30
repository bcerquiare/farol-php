<?php

namespace Farol\Classes\Mvc;

use Farol\Traits\Mvc\Model\CrudModel;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel{

    use CrudModel;

    protected $table        = 'table_name';
    protected $primaryKey   = 'primary_key_name';
    public $timestamps      = false;
    protected $dateFormat   = 'U';
    protected $crudInstance = null;

}
