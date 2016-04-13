<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:30 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Coin\Money\Transact;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class i_icstats extends Command
{
    public function i_icstats()
    {
        $Transact = new Transact($this->db);

        $out = "There are:"
            . "\n`   `•` " . $Transact->CoinSQL->getTotalCoinExisting(true) . "` *" . COIN_CURRENCY_NAME . "* in existence"
            . "\n`   `•` " . $Transact->CoinSQL->GetNumberOfTransactions() . "` logged transactions"
            . "\n`   `•` " . $Transact->UserSQL->GetTotalNumberOfUsers(false) . "` registered users"
            . "\n*" . COIN_TAXATION_BODY . "* is collecting:"
            . "\n`   `• " . "a transaction tax of `" . COIN_TRANSACTION_TAX * 100 . "%`."
            . "\n`   `• " . "a daily tax of `" . COIN_PERIODIC_TAX * 100 . "%` at `12 noon`";

        Telegram::talk($this->Message->Chat->id,$out);
    }
}