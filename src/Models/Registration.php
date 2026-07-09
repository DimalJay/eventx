<?php
namespace Models;

use Models\BaseModel;
use database\Column;
use database\Table;
use DateTime;

#[Table(name: 'Registrations')]
class Registration extends BaseModel
{
  #[Column(type: 'INT', nullable: false, primaryKey: true, autoIncrement: true)]
  protected int $id;

  #[Column(type: 'INT', nullable: false)]
  protected int $eventId;

  #[Column(type: 'INT', nullable: false)]
  protected int $userId;

  #[Column(type: 'DATETIME', default: 'CURRENT_TIMESTAMP')]
  protected DateTime $registeredAt;

  #[Column(type: 'VARCHAR', length: 100, nullable: false)]
  protected string $ticketCode;

  #[Column(type: 'BOOLEAN', nullable: false, default: false)]
  protected bool $inWaitlist = false;

  #[Column(type: 'VARCHAR', length: 100, nullable: false, default: "'PENDING'")]
  protected string $status = 'PENDING';

  #[Column(type: 'DATETIME', nullable: true)]
  protected DateTime $chekingTime;

  public function __construct($eventId, $userId) {
    $this->eventId = $eventId;
    $this->userId = $userId;
    $this->ticketCode = $ticketCode ?? uniqid();
    $this->registeredAt = new DateTime();
    parent::__construct();
  }

  public static function empty() : self {
    return new self(0,0);
  }

  public function getInWaitlist() : bool {
    return $this->inWaitlist;
  }

  public function setInWaitlist(bool $inWaitlist) : void {
    $this->inWaitlist = $inWaitlist;
  }

  public function getEventId() : int {
    return $this->eventId;
  }
}
