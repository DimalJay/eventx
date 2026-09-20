<?php

namespace Models;

use Models\BaseModel;
use database\Column;
use database\Table;
use DateTime;

#[Table(name: 'team_labels')]
class TeamLabel extends BaseModel
{
  #[Column(type: 'INT', nullable: false, primaryKey: true, autoIncrement: true)]
  protected int $id;

  #[Column(type: 'INT', nullable: false, unique: true)]
  protected int $eventId;

  #[Column(type: 'TEXT', nullable: true)]
  protected ?string $labels = null;

  #[Column(type: 'DATETIME', default: 'CURRENT_TIMESTAMP')]
  protected DateTime $updatedAt;

  public function __construct($eventId = 0, $labels = null)
  {
    $this->eventId = $eventId;
    $this->labels = $labels;
    $this->updatedAt = new DateTime();
    parent::__construct();
  }

  public static function empty(): self
  {
    return new self();
  }
}