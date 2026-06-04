<?php
namespace Models;

// require 'BaseModel.php';

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

  #[Column(type: 'VARCHAR', length: 150, nullable: false)]
  protected string $teamName;

  #[Column(type: 'INT', nullable: false)]
  protected int $managedBy;

  #[Column(type: 'DATETIME', default: 'CURRENT_TIMESTAMP')]
  protected DateTime $createdAt;

  #[Column(type: 'INT', nullable: false)]
  protected DateTime $createdBy;

  public function __construct($eventId, $teamName, $managedBy, $createdBy) {
    $this->eventId = $eventId;
    $this->teamName = $teamName;
    $this->managedBy = $managedBy;
    $this->createdBy = $createdBy;
    $this->createdAt = new DateTime();
    parent::__construct();
  } 
  
  public static function empty() : self {
    return new self(0,"",0,0);
  }
  











}
?>
