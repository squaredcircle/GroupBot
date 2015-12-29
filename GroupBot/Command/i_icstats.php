<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:30 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Coin\Coin;
use GroupBot\Types\Command;

class i_icstats extends Command
{
    public function i_icstats()
    {
        $Coin = new Coin();

        $out = "";

        $out .= "There are:"
            . "\n`   `•` " . $Coin->Transact->Validate->getTotalCoinExisting(true) . "` *Isaac Coins* in existence"
            . "\n`   `•` " . $Coin->SQL->GetNumberOfTransactions() . "` logged transactions"
            . "\n`   `•` " . $Coin->SQL->GetTotalNumberOfUsers(false) . "` registered users"
            . "\n*Israel* is collecting:"
            . "\n`   `• " . "a transaction tax of `" . COIN_TRANSACTION_TAX * 100 . "%`."
            . "\n`   `• " . "a daily tax of `" . COIN_PERIODIC_TAX * 100 . "%` at `12 noon`";

        $this->Telegram->talk($this->Message->Chat->id,$out);
    }
}