<?php
namespace Models;

use Models\BaseModel;
use database\Column;
use database\Table;
use DateTime;

#[Table(name: 'guests')]
class Guest extends BaseModel
{
    #[Column(type: 'INT', nullable: false, primaryKey: true, autoIncrement: true)]
    protected int $id;

    #[Column(type: 'INT', nullable: false)]
    protected int $eventId;

    #[Column(type: 'VARCHAR', length: 255, nullable: false)]
    protected string $email;

    #[Column(type: 'VARCHAR', length: 100, nullable: false)]
    protected string $role;

    #[Column(type: 'VARCHAR', length: 50, nullable: false, default: "'invited'")]
    protected string $status = 'invited';

    #[Column(type: 'DATETIME', default: 'CURRENT_TIMESTAMP')]
    protected DateTime $createdAt;

    public function __construct(int $eventId = 0, string $email = '', string $role = '', string $status = 'invited')
    {
        $this->eventId = $eventId;
        $this->email = $email;
        $this->role = $role;
        $this->status = $status;
        $this->createdAt = new DateTime();
        parent::__construct();
    }

    public static function empty(): self
    {
        return new self(0, '', '', 'invited');
    }

    public function getEventId(): int
    {
        return $this->eventId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
