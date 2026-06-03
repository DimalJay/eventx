<?php

namespace Models;

require "./database/SchemaGenerator.php";
require "./database/database.php";

use database\SchemaGenerator;
use database\Database;

abstract class BaseModel { 
    private SchemaGenerator $schemaGenerator;  

    public function __construct() {
        $this->schemaGenerator = new SchemaGenerator(static::class);
    }     

    abstract public static function empty(): self;

    public static function createClass(){
        $schema = new SchemaGenerator(static::class);
        $db = new Database();
        $q = $schema->createTable();
        return $db->query($q);
    }

    public function save() {
        $db = new Database();
        $q = $this->schemaGenerator->insertRecord($this);
        return $db->query($q);
    }

    public static function selectAll() {
        $schema = new SchemaGenerator(static::class);
        $db = new Database();
        $q = $schema->selectAll();
        return $db->queryAll($q);
    }

    public static function where(array $conditions) {
        $schema = new SchemaGenerator(static::class);
        $db = new Database();
        $q = $schema->where($conditions);
        return $db->query($q);
    }

    public static function updateRecord($field, $value){
        $schema = new SchemaGenerator(static::class);
        $db = new Database();
        $q = $schema->updateRecord($field, $value);
        return $db->query($q);
    }

    public static function deleteRecord($field){
        $schema = new SchemaGenerator(static::class);
        $db = new Database();
        $q = $schema->deleteRecord($field);
        return $db->query($q);
    }

}
