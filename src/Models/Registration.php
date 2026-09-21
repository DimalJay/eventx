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

  #[Column(type: 'INT', nullable: true)]
  protected ?int $ticketId = null;

  #[Column(type: 'VARCHAR', length: 100, nullable: false, default: "'PENDING'")]
  protected string $status = 'PENDING';

  #[Column(type: 'DATETIME', nullable: true)]
  protected DateTime $chekingTime;

  #[Column(type: 'TEXT', nullable: true)]
  protected ?string $customFields = null;

  public function __construct($eventId, $userId, $customFields = null) {
    $this->eventId = $eventId;
    $this->userId = $userId;
    $this->customFields = self::encodeCustomFields($customFields);
    $this->registeredAt = new DateTime();
    parent::__construct();
  }

  /**
   * Normalize submitted custom field data into a stored JSON string.
   * Accepts an array/object or an already-encoded JSON string.
   */
  public static function encodeCustomFields($value): ?string
  {
      if ($value === null || $value === '') {
          return null;
      }
      if (is_array($value) || is_object($value)) {
          return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      }
      $decoded = json_decode((string) $value, true);
      if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
          return (string) $value;
      }
      return null;
  }

  public static function empty() : self {
    return new self(0,0);
  }

  public function setInWaitlist() : void {
    $this->status = "WAITLIST";
  }

  public function getEventId() : int {
    return $this->eventId;
  }

  public function getUserId() : int {
    return $this->userId;
  }
}
