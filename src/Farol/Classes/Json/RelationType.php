<?php
namespace Farol\Classes\Json;

use Illuminate\Database\Eloquent\Model;

class RelationType{

    const RESULT_AS_MODEL = 0;
    const RESULT_AS_ARRAY = 1;

    protected $raw;
    protected $resultType;
    protected $relationName;
    protected $columns;
    protected $asName;

    public function __construct( $raw ){
        $this->raw = $raw;
        $this->proccess($raw);
    }

    protected function convertResultTypeToConst($type){

        switch($type){
            case "array":   return self::RESULT_AS_ARRAY; break;
            default:        return self::RESULT_AS_MODEL; break;
        }

    }

    protected function proccess($raw){

        $explode            = \explode(":", $raw);
        $relationName       = $explode[0];
        $asName             = $relationName;
        $typeString         = $explode[1];
        $resultTypeParts    = \explode("as ", $typeString);
        $resultType         = $this->convertResultTypeToConst( \trim($resultTypeParts[0]) );

        if( $resultTypeParts[1] ){
            $asName = $resultTypeParts[1];
        }

        $this->relationName = $relationName;
        $this->resultType = $resultType;
        $this->asName = $asName;

    }

    public function relationName(){
        return $this->relationName;
    }

    public function relationNameAlias(){
        return ( $this->asName ? $this->asName : $this->relationName );
    }

    public function resultType(){
        return $this->resultType;
    }

    public function fetchRelation( Model $model ){

    }

}
