<?php

namespace app\Models;

require 'BaseModel.php';

use app\Models\BaseModel;
use database\Column;
use database\Table;


#[Table(name: 'users')]
class User extends BaseModel{
    #[Column(type: 'INT', nullable: false, primaryKey: true, autoIncrement: true)]
    protected int $id;
    
    #[Column(type: 'VARCHAR', length: 150, nullable: false)]
    protected string $name;
    
    #[Column(type: 'INT', nullable: false)]
    protected int $age;
}
