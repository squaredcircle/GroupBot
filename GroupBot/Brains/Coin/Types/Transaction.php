<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 28/12/2015
 * Time: 3:45 PM
 */

namespace GroupBot\Brains\Coin\Types;


use GroupBot\Brains\Coin\Enums\TransactionType;

class Transaction
{
    public $date, $user_sending, $user_receiving, $amount, $type;

    public function __construct($date, CoinUser $user_sending, CoinUser $user_receiving, $amount, TransactionType $type)
    {
        $this->date = $date;
        $this->user_sending = $user_sending;
        $this->user_receiving = $user_receiving;
        $this->amount = $amount;
        $this->type = $type;
    }
}