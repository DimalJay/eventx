<?php

namespace Models;

use Models\BaseModel;
use database\Column;
use database\Table;
use DateTime;

#[Table(name: 'Teams')]
class Team extends BaseModel
{
  #[Column(type: 'INT', nullable: false, primaryKey: true, autoIncrement: true)]
  protected int $id;

  #[Column(type: 'INT', nullable: false)]
  protected int $eventId;

  #[Column(type: 'INT', nullable: false)]
  protected int $userId;

  #[Column(type: 'DATETIME', default: 'CURRENT_TIMESTAMP')]
  protected DateTime $joinedAt;

  #[Column(type: 'VARCHAR', length: 100, nullable: false)]
  protected string $role;

  public function __construct($eventId, $userId, $role)
  {
    $this->eventId = $eventId;
    $this->userId = $userId;
    $this->role = $role;
    $this->joinedAt = new DateTime();
    parent::__construct();
  }

  public static function empty(): self
  {
    return new self(0, 0, "");
  }
}
