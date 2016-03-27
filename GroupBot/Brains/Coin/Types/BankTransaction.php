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

class BankTransaction
{
    public $date, $user, $amount, $type;

    public function __construct(User $user, $amount, TransactionType $type, $date = NULL)
    {
        $this->date = $date;
        $this->user = $user;
        $this->amount = $amount;
        $this->type = $type;
    }
}