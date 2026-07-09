<?php
namespace Models;

use Models\BaseModel;
use database\Column;
use database\Table;
use DateTime;

#[Table(name: 'Payments')]
class Payment extends BaseModel
{
 #[Column(type: 'INT', nullable: false, primaryKey: true, autoIncrement: true)]
 protected int $id; 

 #[Column(type: 'INT', nullable: false)]
  protected int $userId;

 #[Column(type: 'INT', nullable: false)]
  protected int $registerId;

 #[Column(type: 'DECIMAL(10,2)', nullable: false)]
  protected float $amount;

 #[Column(type: 'DATETIME', default: 'CURRENT_TIMESTAMP' )]
  protected DateTime $paymentAt;

  public function __construct($userId, $registerId, $amount) {
      $this->userId = $userId;
      $this->registerId = $registerId;
      $this->amount = $amount;
      $this->paymentAt = new DateTime();
      parent::__construct();
    } 

  public static function empty() : self {
    return new self(0,0,0);
}
}
