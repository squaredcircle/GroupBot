<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 28/12/2015
 * Time: 3:45 PM
 */

namespace GroupBot\Brains\Coin\Types;


use GroupBot\Brains\Coin\Enums\TransactionType;
use GroupBot\Types\User;

class Transaction
{
    public $date, $user_sending, $user_receiving, $amount, $type;

    public function __construct(User $user_sending = NULL, User $user_receiving = NULL, $amount, TransactionType $type, $date = NULL)
    {
        $this->date = $date;
        $this->user_sending = $user_sending;
        $this->user_receiving = $user_receiving;
        $this->amount = $amount;
        $this->type = $type;
    }
}