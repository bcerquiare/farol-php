<?php

namespace Farol\Traits\Mvc\Model;

use Farol\Classes\Mvc\Model\CrudModel as ClassCrudModel;

trait CrudModel{

    public function crudClassName() : string{

        $model = \get_class($this);
        $modelParts = \explode("\\", $model);
        $modelNamespace = $modelParts[3];
        $modelCrudParts = \array_slice( $modelParts, 0, -1 );
        $modelCrudParts[] = $modelNamespace."Crud";

        return \implode("\\", $modelCrudParts);

    }

    public function crud() : ClassCrudModel{

        if( is_null( $this->crudInstance ) ){
            $name = $this->crudClassName();
            $this->crudInstance = new $name( $this );
        }

        return $this->crudInstance;

    }

}
