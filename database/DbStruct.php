<?php

namespace database;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Table {
    public function __construct(public string $name) {}
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column {
    public function __construct(
        public string $type,
        public ?int $length = null,
        public bool $nullable = false,
        public bool $primaryKey = false,
        public bool $autoIncrement = false,
        public bool|string|null $default = null,
        public bool $unique = false,
    ) {}
}