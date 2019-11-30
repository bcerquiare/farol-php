<?php

use Farol\Classes\Json\JsonMaker;

if (!function_exists('json')) {

    function json() : JsonMaker{
        return new JsonMaker();
    }

}
