<?php

namespace Models;

use Models\BaseModel;
use database\Column;
use database\Table;
use DateTime;


#[Table(name: 'feedbacks')]
class Feedback extends BaseModel
{
   #[Column(type: 'INT', nullable: false, primaryKey: true, autoIncrement: true)]
    protected int $id;

    #[Column(type: 'INT', nullable: false)]
    protected int $eventId;

    #[Column(type: 'INT', nullable: false)]
    protected int $participantId;

    #[Column(type: 'TINYINT', nullable: false)]
    protected int $organizationRating;

    #[Column(type: 'TINYINT', nullable: false)]
    protected int $contentRating;

    #[Column(type: 'TINYINT', nullable: false)]
    protected int $experienceRating;

    #[Column(type: 'TEXT', nullable: true)]
    protected ?string $comment = null;

    #[Column(type: 'VARCHAR', length: 20, nullable: false, default: "'Pending'")]
    protected string $sentiment = 'Pending';

    #[Column(type: 'DATETIME', default: 'CURRENT_TIMESTAMP')]
    protected DateTime $createdAt;

    #[Column(type: 'DATETIME', nullable: true)]
    protected ?DateTime $updatedAt = null;

    public function __construct(
        int $eventId, int $participantId, int $organizationRating, int $contentRating, int $experienceRating, ?string $comment = null, string $sentiment = 'Pending') 
        {
        $this->eventId = $eventId;
        $this->participantId = $participantId;
        $this->organizationRating = $organizationRating;
        $this->contentRating = $contentRating;
        $this->experienceRating = $experienceRating;
        $this->comment = $comment;
        $this->sentiment = $sentiment;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();

        parent::__construct();
    }

    public static function empty(): self
    {
        return new self("","","","","");
    }
    }