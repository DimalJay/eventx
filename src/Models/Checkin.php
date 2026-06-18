<?php
namespace Models;

use Models\BaseModel;
use database\Column;
use database\Table;
use DateTime;

#[Table(name: 'checkin')]
class Checkin extends BaseModel
{
  #[Column(type: 'INT', nullable: false, primaryKey: true, autoIncrement: true)]
  protected int $id;

  #[Column(type: 'INT', nullable: false)]
  protected int $registrationId;

  #[Column(type: 'DATETIME', default: 'CURRENT_TIMESTAMP')]
  protected DateTime $checkinAt;

  #[Column(type: 'INT', nullable: false)]
  protected int $eventId;

  #[Column(type: 'INT', nullable: false)]
  protected int $userId;

  #[Column(type: 'VARCHAR', length: 255, nullable: false)]
  protected string $verifyMethod;

  public function __construct($registrationId, $eventId, $userId, $verifyMethod) {
    $this->registrationId = $registrationId;
    $this->eventId = $eventId;
    $this->userId = $userId;
    $this->verifyMethod = $verifyMethod;
    $this->checkinAt = new DateTime();
    parent::__construct();
  }

  public static function empty() : self {
    return new self(0,0,0,"");
  }
} 