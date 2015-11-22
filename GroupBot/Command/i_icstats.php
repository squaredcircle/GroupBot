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

        $out .= "There are " . $ic->moneyControl->getTotalMoney(true) . " Isaac Coins in existance.\n";
        $out .= "There are " . $ic->moneyControl->getTotalTransactions() . " logged transactions\n";
        $out .= "There are " . $ic->loginControl->getTotalUsers() . " registered users\n\n";
        $out .= "The transaction tax to Israel is at " . TRANSACTION_TAX * 100 . "%.\n";
        $out .= "The daily tax to Israel is at " . PERIODIC_TAX * 100 . "%\n";
        $out .= "The daily tax is collected at 12 noon";

        $this->Telegram->talk($this->Message->Chat->id,$out);
    }
}