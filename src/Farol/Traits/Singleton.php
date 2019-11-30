<?php

namespace Farol\Traits;

trait Singleton
{
    /**
     * Singleton pattern implementation
     * @return mixed
     */
    public static function Instance(){

        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;

    }

}
