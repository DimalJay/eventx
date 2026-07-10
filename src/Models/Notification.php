<?php

namespace Models;

use Models\BaseModel;
use database\Column;
use database\Table;
use DateTime;

#[Table(name: 'notifications')]
class Notification extends BaseModel
{
    #[Column(type: 'INT', nullable: false, primaryKey: true, autoIncrement: true)]
    protected int $id;

    #[Column(type: 'VARCHAR', length: 255, nullable: false)]
    protected string $title;

    #[Column(type: 'TEXT', nullable: false)]
    protected string $message;

    #[Column(type: 'VARCHAR', length: 50, nullable: false, default: "'WARN'")]
    protected string $status = 'WARN';

    #[Column(type: 'INT', nullable: false)]
    protected int $userId;

    #[Column(type: 'VARCHAR', length: 50, nullable: false)]
    protected string $type;

    #[Column(type: 'DATETIME', default: 'CURRENT_TIMESTAMP')]
    protected DateTime $createdAt;

    #[Column(type: 'DATETIME', nullable: true)]
    protected ?DateTime $readAt = null;

    #[Column(type: 'BOOLEAN', nullable: false, default: false)]
    protected bool $isRead = false;

    #[Column(type: 'JSON', nullable: true)]
    protected ?string $extras = null;

    public function setExtras(mixed $data): void
    {
        $this->extras = is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function getExtras(): mixed
    {
        return $this->extras ? json_decode($this->extras, true) : null;
    }

    public function __construct(int $userId, string $title, string $message, string $type, mixed $extras = null) {
        $this->userId = $userId;
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->setExtras($extras);
        $this->createdAt = new DateTime();

        parent::__construct();
    }

    public static function empty(): self
    {
        return new self(0, "", "", "");
    }
}