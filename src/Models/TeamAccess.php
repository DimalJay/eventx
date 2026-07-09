<?php

namespace Models;

use Models\BaseModel;
use database\Column;
use database\Table;
use DateTime;

#[Table(name: 'team_access')]
class TeamAccess extends BaseModel
{
  #[Column(type: 'INT', nullable: false, primaryKey: true, autoIncrement: true)]
  protected int $id;

  #[Column(type: 'INT', nullable: false)]
  protected int $userId;

  #[Column(type: 'INT', nullable: false)]
  protected int $eventId;

  #[Column(type: 'VARCHAR', length: 20, nullable: false)]
  protected string $role;

  #[Column(type: 'VARCHAR', length: 20, nullable: false, default: "'PENDING'")]
  protected string $status = 'PENDING';

  #[Column(type: 'DATETIME', default: 'CURRENT_TIMESTAMP')]
  protected DateTime $joinedAt;

  public function __construct($userId, $eventId, $role)
  {
    $this->userId = $userId;
    $this->eventId = $eventId;
    $this->role = $role;
    $this->joinedAt = new DateTime();
    parent::__construct();
  }

  public static function empty(): self
  {
    return new self(0, 0, 0);
  }
}
