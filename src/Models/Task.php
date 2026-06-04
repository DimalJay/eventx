<?php

namespace Models;

use Models\BaseModel;
use database\Column;
use database\Table;
use DateTime;

#[Table(name: 'tasks')]
class Task extends BaseModel
{
    #[Column(type: 'INT', nullable: false, primaryKey: true, autoIncrement: true)]
    protected int $id;

    #[Column(type: 'INT', nullable: false)]
    protected int $eventId;

    #[Column(type: 'VARCHAR', length: 255, nullable: false)]
    protected string $title;

    #[Column(type: 'TEXT', nullable: true)]
    protected ?string $description = null;

    #[Column(type: 'INT', nullable: false)]
    protected int $assignedTo;

    #[Column(type: 'INT', nullable: false)]
    protected int $assignedBy;

    #[Column(type: 'DATETIME', nullable: true)]
    protected ?DateTime $createddAt = null;

    #[Column(type: 'DATETIME', nullable: true)]
    protected ?DateTime $assignedDate = null;

    #[Column(type: 'DATETIME', nullable: true)]
    protected ?DateTime $dueDate = null;

    #[Column(type: 'DATETIME', nullable: true)]
    protected ?DateTime $updatedAt = null;

    #[Column(type: 'VARCHAR', length: 255, nullable: false, default: "TODO")]
    protected string $status = "TODO";

    public function __construct($eventId, $title, $description, $assignedTo, $assignedBy, $dueDate,$status = 'TODO') {
        $this->eventId = $eventId;
        $this->title = $title;
        $this->description = $description;
        $this->assignedTo = $assignedTo;
        $this->assignedBy = $assignedBy;
        $this->createddAt = new DateTime();
        $this->assignedDate = new DateTime();
        $this->dueDate = new DateTime($dueDate);
        $this->updatedAt = new DateTime();
        $this->status = $status;

        parent::__construct();
    }

    public static function empty() : self {
        return new self("", "", "", "", "", "");
    }
}
