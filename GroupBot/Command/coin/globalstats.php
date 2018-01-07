<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:30 AM
 */
namespace GroupBot\Command\coin;

use GroupBot\Brains\Coin\Money\Transact;
use GroupBot\Database\User;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class globalstats extends Command
{
    public function main()
    {
        $Transact = new Transact($this->db);
        $Users = new User($this->db);

        $out = "There are:"
            . "\n`   `•` " . $Transact->CoinSQL->getTotalCoinExisting(true) . "` *" . COIN_CURRENCY_NAME . "s* in existence"
            . "\n`   `•` " . $Users->getTotalNumberOfUsers(false) . "` registered users";

        Telegram::talk($this->Message->Chat->id,$out);
    }
}