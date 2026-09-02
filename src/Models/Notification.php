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

  #[Column(type: 'DATETIME', nullable: false, default: 'CURRENT_TIMESTAMP')]
  protected DateTime $createdAt;

  #[Column(type: 'DATETIME', nullable: true)]
  protected ?DateTime $readAt = null;

  #[Column(type: 'TINYINT', nullable: false, default: 0)]
  protected bool $isRead = false;

  #[Column(type: 'LONGTEXT', nullable: true)]
  protected ?string $extras = null;

  public function __construct(string $title, string $message, int $userId, string $type = 'General', ?string $extras = null, bool $isRead = false)
  {
    $this->title = $title;
    $this->message = $message;
    $this->userId = $userId;
    $this->type = $type;
    $this->extras = $extras;
    $this->isRead = $isRead;
    $this->status = $isRead ? 'read' : 'unread';
    $this->createdAt = new DateTime();
    parent::__construct();
  }

  public static function empty(): self
  {
    return new self("", "", 0);
  }
}
