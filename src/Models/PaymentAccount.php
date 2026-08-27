<?php

namespace Models;

use Models\BaseModel;
use database\Column;
use database\Table;
use DateTime;

#[Table(name: 'payment_accounts')]
class PaymentAccount extends BaseModel
{
    #[Column(type: 'INT', nullable: false, primaryKey: true, autoIncrement: true)]
    protected int $id;

    #[Column(type: 'INT', nullable: false, unique: true)]
    protected int $userId;

    #[Column(type: 'VARCHAR', length: 100, nullable: false)]
    protected string $accountId;

    #[Column(type: 'VARCHAR', length: 255, nullable: false)]
    protected string $email;

    #[Column(type: 'BOOLEAN', nullable: false, default: '0')]
    protected bool $isConnected = false;

    #[Column(type: 'TEXT', nullable: true)]
    protected ?string $accountDetails = null;

    #[Column(type: 'DATETIME', default: 'CURRENT_TIMESTAMP')]
    protected DateTime $createdAt;

    #[Column(type: 'DATETIME', nullable: true)]
    protected ?DateTime $updatedAt = null;

    #[Column(type: 'DATETIME', nullable: true)]
    protected ?DateTime $connectedAt = null;

    public function __construct($userId = 0, $accountId = "", $email = "")
    {
        $this->userId = $userId;
        $this->accountId = $accountId;
        $this->email = $email;
        $this->isConnected = false;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        parent::__construct();
    }

    public static function empty(): self
    {
        return new self();
    }
}