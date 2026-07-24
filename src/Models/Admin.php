<?php

namespace Models;

use Models\BaseModel;
use database\Column;
use database\Table;
use DateTime;

#[Table(name: 'admins')]
class Admin extends BaseModel
{
    #[Column(type: 'INT', nullable: false, primaryKey: true, autoIncrement: true)]
    protected int $id;

    #[Column(type: 'VARCHAR', length: 50, nullable: false, unique: true)]
    protected string $email;

    #[Column(type: 'VARCHAR', length: 150, nullable: false)]
    protected string $firstName;

    #[Column(type: 'VARCHAR', length: 150, nullable: false)]
    protected string $lastName;

    #[Column(type: 'VARCHAR', length: 255, nullable: false)]
    protected string $password;

    #[Column(type: 'DATETIME', default: 'CURRENT_TIMESTAMP')]
    protected DateTime $createdAt;

    #[Column(type: 'DATETIME', nullable: true)]
    protected DateTime $updatedAt;

    public function __construct($email, $firstName, $lastName, $password)
    {
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->password = password_hash($password, PASSWORD_BCRYPT);
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        parent::__construct();
    }

    public static function empty(): self
    {
        return new self("", "", "", "");
    }
}
