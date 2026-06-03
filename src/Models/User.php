<?php

namespace Models;

require 'BaseModel.php';

use Models\BaseModel;
use database\Column;
use database\Table;
use DateTime;


#[Table(name: 'users')]
class User extends BaseModel
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

    #[Column(type: 'VARCHAR', length: 50, nullable: false)]
    protected string $loginType;

    #[Column(type: 'BOOLEAN', nullable: false, default: true)]
    protected bool $isVerified;

    #[Column(type: 'VARCHAR', length: 50, nullable: false)]
    protected string $role;

    #[Column(type: 'TEXT', nullable: true)]
    protected ?string $profilePicture = null;

    #[Column(type: 'VARCHAR', length: 20, nullable: false, default: "'active'")]
    protected string $accountStatus = 'active';

    #[Column(type: 'DATETIME', default: 'CURRENT_TIMESTAMP')]
    protected DateTime $createdAt;

    #[Column(type: 'DATETIME', nullable: true)]
    protected DateTime $updatedAt;

    #[Column(type: 'DATETIME', nullable: true)]
    protected ?DateTime $lastLogin = null;

    public function __construct($email, $firstName, $lastName, $password, $profilePicture, $loginType = 'standard', $role = 'user') {
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->password = password_hash($password, PASSWORD_BCRYPT);
        $this->profilePicture = $profilePicture;
        $this->loginType = $loginType;
        $this->role = $role;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->lastLogin = new DateTime();
        parent::__construct();
    }

    public static function empty() : self {
        return new self("", "", "", "", "");
    }
}
