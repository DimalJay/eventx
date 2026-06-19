<?php

namespace Models;

use Models\BaseModel;
use database\Column;
use database\Table;
use DateTime;

#[Table(name: 'events')]
class Event extends BaseModel
{
  #[Column(type: 'INT', nullable: false, primaryKey: true, autoIncrement: true)]
  protected int $id;

  #[Column(type: 'VARCHAR', length: 255, nullable: false)]
  protected string $title;

  #[Column(type: 'VARCHAR', length: 100, nullable: false, default: "'online'")]
  protected string $eventType;

  #[Column(type: 'TEXT', nullable: true)]
  protected ?string $description = null;

  #[Column(type: 'DATETIME', nullable: false)]
  protected DateTime $startDate;

  #[Column(type: 'DATETIME', nullable: false)]
  protected DateTime $endDate;

  #[Column(type: 'VARCHAR', length: 255, nullable: true)]
  protected ?string $location = null;

  #[Column(type: 'INT', nullable: false)]
  protected int $organizerId;

  #[Column(type: 'VARCHAR', length: 255, nullable: true)]
  protected ?string $coverImage = null;

  #[Column(type: 'BOOLEAN', nullable: false, default: true)]
  protected bool $isPublic = true;

  #[Column(type: 'DATETIME', default: 'CURRENT_TIMESTAMP')]
  protected DateTime $createdAt;

  #[Column(type: 'DATETIME', default: 'CURRENT_TIMESTAMP')]
  protected DateTime $updatedAt;

  #[Column(type: 'INT', nullable: false, default: 0)]
  protected int $capacity = 0;

  #[Column(type: 'INT', nullable: false)]
  protected float $ticketPrice = 0.00;

  #[Column(type: 'DATETIME', nullable: true)]
  protected ?DateTime $regDeadline = null;

  #[Column(type: 'TEXT', nullable: true)]
  protected ?string $agenda = null;

  #[Column(type: 'BOOLEAN', nullable: false, default: false)]
  protected bool $waitlistEnabled = false;


    public function __construct($title, $eventType, $description, $startDate, $endDate, $location, $organizerId, $coverImage, $isPublic, $capacity, $ticketPrice, $regDeadline, $agenda, $waitlistEnabled = false) {
      $this->title = $title;
      $this->eventType = $eventType;
      $this->description = $description;
      $this->startDate = new DateTime($startDate);
      $this->endDate = new DateTime($endDate);
      $this->location = $location;
      $this->organizerId = $organizerId;
      $this->coverImage = $coverImage;
      $this->isPublic = $isPublic;
      $this->capacity = $capacity;
      $this->ticketPrice = $ticketPrice;
      if ($regDeadline) {
        $this->regDeadline = new DateTime($regDeadline);
      }
      $this->agenda = $agenda;
      $this->waitlistEnabled = $waitlistEnabled;
      $this->createdAt = new DateTime();
      $this->updatedAt = new DateTime();
      parent::__construct();
  }

  public static function empty() : self {
    return new self(null, null, null, null, null, null, 0, null, false, 0, 0.0, null, null);
  }
}