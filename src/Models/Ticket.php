<?php

namespace Models;

use Models\BaseModel;
use database\Column;
use database\Table;
use DateTime;

#[Table(name: 'tickets')]
class Ticket extends BaseModel
{
  #[Column(type: 'INT', nullable: false,  primaryKey: true, autoIncrement: true)]
  protected int $id;

  #[Column(type: 'INT', nullable: false)]
  protected int $eventId;

  #[Column(type: 'INT', nullable: false)]
  protected int $userId;

  #[Column(type: 'INT', nullable: true)]
  protected int $paymentId = 0;

  #[Column(type: 'INT', nullable: false)]
  protected int $registerId;

  #[Column(type: 'VARCHAR', length: 100, nullable: false)]
  protected string $ticketCode;

  public function __construct($eventId, $userId, $ticketCode, $registerId, $paymentId = 0) {
    $this->eventId = $eventId;
    $this->userId = $userId;
    $this->ticketCode = $ticketCode;
    $this->paymentId = $paymentId;
    $this->registerId = $registerId;
    parent::__construct();
  } 

  public static function empty() : self {
    return new self(0,0,'',0);
  }




  
}
