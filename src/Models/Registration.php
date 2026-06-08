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

  #[Column(type: 'VARCHAR', length: 50, nullable: false)]
  protected string $status;

  #[Column(type: 'DATETIME', default: 'CURRENT_TIMESTAMP')]
  protected DateTime $createdAt;

  #[Column(type: 'DATETIME', default: 'CURRENT_TIMESTAMP')]
  protected DateTime $updatedAt;

  #[Column(type: 'BOOLEAN', nullable: false, default: false)]
  protected bool $checkedIn;

  #[Column(type: 'BOOLEAN', nullable: false, default: false)]
  protected bool $waitlisted;

  #[Column(type: 'INT', nullable: true)]
  protected ?int $paymentId;

  public function __construct($eventId, $userId, $status = 'registered', $checkedIn = false, $waitlisted = false, $paymentId = null) {
    $this->eventId = $eventId;
    $this->userId = $userId;
    $this->status = $status;
    $this->createdAt = new DateTime();
    $this->updatedAt = new DateTime();
    $this->checkedIn = $checkedIn;
    $this->waitlisted = $waitlisted;
    $this->paymentId = $paymentId;

    parent::__construct();
  } 

  public static function empty(): self
  {
    return new self(0, 0);
  }

  
  



  
}
  


 
