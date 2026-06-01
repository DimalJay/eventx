<?php

namespace app\Models;

require "./database/SchemaGenerator.php";
use database\SchemaGenerator;

abstract class BaseModel {        
    public function createClass(){
        echo SchemaGenerator::createTable(static::class);
    }

}
