<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:30 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Coin;
use GroupBot\Types\Command;

class i_icstats extends Command
{
    public function i_icstats()
    {
        $ic = new Coin();
        $ic = $ic->getObject();

        $out = "";

        $out .= "There are:"
            . "\n`   `•` " . $ic->Check->getTotalMoneyExisting(true) . "` *Isaac Coins* in existence"
            . "\n`   `•` " . $ic->Check->getTotalTransactions() . "` logged transactions"
            . "\n`   `•` " . $ic->UserControl->getTotalUsers() . "` registered users"
            . "\n*Israel* is collecting:"
            . "\n`   `• " . "a transaction tax of `" . TRANSACTION_TAX * 100 . "%`."
            . "\n`   `• " . "a daily tax of `" . PERIODIC_TAX * 100 . "%` at `12 noon`";

        $this->Telegram->talk($this->Message->Chat->id,$out);
    }
}