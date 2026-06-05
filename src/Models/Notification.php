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
    protected string $notificationTitle;

    #[Column(type: 'TEXT', nullable: false)]
    protected string $content;

    #[Column(type: 'INT', nullable: false)]
    protected int $eventId;

    #[Column(type: 'INT', nullable: false)]
    protected int $receiverId;

    #[Column(type: 'VARCHAR', length: 50, nullable: false, default: "'General'")]
    protected string $notificationType= 'General';

    #[Column(type: 'DATETIME', nullable: false)]
    protected DateTime $notificationTime;

    #[Column(type: 'BOOLEAN', nullable: false, default: false)]
    protected bool $isRead = false;

    #[Column(type: 'DATETIME', nullable: true)]
    protected ?DateTime $sentAt = null;

    #[Column(type: 'DATETIME', default: 'CURRENT_TIMESTAMP')]
    protected DateTime $createdAt;

    #[Column(type: 'DATETIME', nullable: true)]
    protected ?DateTime $updatedAt = null;

    public function __construct(string $notificationTitle, string $content, int $eventId, int $receiverId, string $notificationType = 'General', $isRead = false) {
        $this->notificationTitle = $notificationTitle;
        $this->content = $content;
        $this->eventId = $eventId;
        $this->receiverId = $receiverId;
        $this->notificationType = $notificationType;
        $this->notificationTime = new DateTime();
        $this->updatedAt = new DateTime();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->isRead = $isRead;

        parent::__construct();
    }

    public static function empty(): self
    {
        return new self("","","","");
    }
}