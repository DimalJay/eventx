<?php

namespace database;

require 'DbStruct.php';

use database\Table;
use database\Column;

class SchemaGenerator {
    private $reflection;
    private $tableName;
    public function __construct(string $className) {
        $this->reflection = new \ReflectionClass($className);
        $tableAttr = $this->reflection->getAttributes(Table::class);
        if (empty($tableAttr)) {
            throw new \Exception("Class is missing the #[Table] attribute.");
        }
        $this->tableName = $tableAttr[0]->newInstance()->name;
    }

    public  function createTable(): string{
        
        
        $columnDefinitions = [];
        
        foreach ($this->reflection->getProperties() as $property){
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
            $uniqueStr = $column->unique ? "UNIQUE" : "";
            $aiStr = $column->autoIncrement ? " AUTO_INCREMENT" : "";
            $pkStr = $column->primaryKey ? " PRIMARY KEY" : "";
            $defaultStr = $column->default ? "DEFAULT {$column->default}": "";

            $columnDefinitions[] = "    `{$columnName}` {$typeStr} {$defaultStr} {$nullStr} {$uniqueStr}{$aiStr}{$pkStr}";
        }
        
        $sql = "CREATE TABLE `{$this->tableName}` (\n";
        $sql .= implode(",\n", $columnDefinitions);
        $sql .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        return $sql;
    }

    public  function updateRecord(array $conditions, array $set): string {
        $setClause = implode(', ', array_map(fn($k, $v) => "`{$k}` = '{$v}'", array_keys($set), $set));
        $whereClause = implode(' AND ', array_map(fn($k, $v) => "`{$k}` = '{$v}'", array_keys($conditions), $conditions));
        return "UPDATE `{$this->tableName}` SET {$setClause} WHERE {$whereClause};";
    }
    
}
