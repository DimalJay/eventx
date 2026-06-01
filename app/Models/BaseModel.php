<?php

namespace app\Models;

require "./database/SchemaGenerator.php";
use database\SchemaGenerator;

abstract class BaseModel { 
    private SchemaGenerator $schemaGenerator;  
    public function __construct() {
        $this->schemaGenerator = new SchemaGenerator(static::class);
    }     
    public function createClass(){
        echo $this->schemaGenerator->createTable();
    }

    public function updateRecord($field, $value){
        echo $this->schemaGenerator->updateRecord($field, $value);
    }

}
