<?php

namespace Models;

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
        $reflection = new \ReflectionClass($this);
        $params = [];
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $attributes = $property->getAttributes(\database\Column::class);
            $autoIncrement = !empty($attributes) ? $attributes[0]->newInstance()->autoIncrement ?? false : false;
            if ($autoIncrement) {
                continue;
            }
            $name = $property->getName();
            $value = $property->isInitialized($this) ? $property->getValue($this) : null;
            if ($value instanceof \DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            } elseif (is_bool($value)) {
                $value = $value ? 1 : 0;
            }
            $params[":{$name}"] = $value;
        }
        $db->execute($q, $params);
        return $db->lastInsertId();
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
        $params = [];
        foreach ($conditions as $k => $v) {
            $params[":{$k}"] = $v;
        }
        return $db->queryAll($q, $params);
    }

    public static function updateRecord($field, $value){
        $schema = new SchemaGenerator(static::class);
        $db = new Database();
        $q = $schema->updateRecord($field, $value);
        $params = [];
        foreach ($field as $k => $v) {
            $params[":cond_{$k}"] = $v;
        }
        foreach ($value as $k => $v) {
            $params[":set_{$k}"] = $v;
        }
        return $db->query($q, $params);
    }

    public static function deleteRecord($field){
        $schema = new SchemaGenerator(static::class);
        $db = new Database();
        $q = $schema->deleteRecord($field);
        $params = [];
        foreach ($field as $k => $v) {
            $params[":{$k}"] = $v;
        }
        return $db->execute($q, $params)->rowCount();
    }

    public static function query($query, $params = []) {
        $db = new Database();
        return $db->queryAll($query, $params);
    }

}
