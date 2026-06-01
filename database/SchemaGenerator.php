<?php

namespace database;

require 'DbStruct.php';

use database\Table;
use database\Column;

class SchemaGenerator {
    public static function createTable(string $className): string{
        $reflection = new \ReflectionClass($className);
        
        $tableAttr = $reflection->getAttributes(Table::class);
        if (empty($tableAttr)) {
            throw new Exception("Class {$className} is missing the #[Table] attribute.");
        }
        $tableName = $tableAttr[0]->newInstance()->name;
        $columnDefinitions = [];
        
        foreach ($reflection->getProperties() as $property){
            $columnAttribute = $property->getAttributes(Column::class);
            if (empty($columnAttribute)) {
                continue;
            }
            
            $column = $columnAttribute[0]->newInstance();
            $columnName = $property->getName();
            $typeStr = $column->type;
            if ($column->length !== null) {
                $typeStr .= "({$column->length})";
            }
            $nullStr = $column->nullable ? "NULL" : "NOT NULL";
            $aiStr = $column->autoIncrement ? " AUTO_INCREMENT" : "";
            $pkStr = $column->primaryKey ? " PRIMARY KEY" : "";

            $columnDefinitions[] = "    `{$columnName}` {$typeStr} {$nullStr}{$aiStr}{$pkStr}";
        }
        
        $sql = "CREATE TABLE `{$tableName}` (\n";
        $sql .= implode(",\n", $columnDefinitions);
        $sql .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        return $sql;
    }
    
}
