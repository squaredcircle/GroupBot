<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 27/12/2015
 * Time: 12:45 AM
 */

namespace GroupBot\Brains\Coin\Types;


class CoinUser
{
    public $user_id;
    public $user_name;
    public $balance;

    public function __construct($user_id, $user_name, $balance)
    {
        $this->user_id = $user_id;
        $this->user_name = $user_name;
        $this->balance = $balance;
    }

    public function getBalance($high_precision = false)
    {
        return $high_precision ? $this->balance : round($this->balance,2);
    }
}