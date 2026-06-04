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

  #[Column(type: 'VARCHAR', length: 255, nullable: false)]
  protected string $name;

  #[Column(type: 'VARCHAR', length: 255, nullable: false)]
  protected string $email;

  #[Column(type: 'VARCHAR', length: 100, nullable: false)]
  protected string $registrationStatus;

  #[Column(type: 'VARCHAR', length: 100, nullable: false)]
  protected string $registrationType;

  public function __construct($eventId, $userId, $name, $email, $registrationStatus, $registrationType) {
    $this->eventId = $eventId;
    $this->userId = $userId;
    $this->name = $name;
    $this->email = $email;
    $this->registrationStatus = $registrationStatus;
    $this->registrationType = $registrationType;
    $this->registeredAt = new DateTime();
    parent::__construct();
  }

  public static function empty() : self {
    return new self(0,0,"","","","");
  }
}