<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:27 AM
 */
namespace GroupBot\Command;

use GroupBot\Base\Telegram;
use GroupBot\Brains\Coin\Coin;
use GroupBot\Types\Command;

class i_check extends Command
{
    public function i_check()
    {
        $Coin = new Coin();

        if ($this->isParam()) {
            if ($balance = $Coin->SQL->GetUserByName($this->Message->text)->getBalance()) {
                Telegram::talk($this->Message->Chat->id, "*" . $this->Message->text . "* has " . emoji("0x1F4B0") . $balance . ", brah");
            } else {
                Telegram::talk($this->Message->Chat->id, emoji("0x1F44E") . " Can't find " . $this->Message->text . " on record, brah");
            }
        } else {
            $balance = $Coin->SQL->GetUserById($this->Message->User->id)->getBalance();
            if ($balance >= 0) {
                Telegram::talk($this->Message->Chat->id, "You've got " . emoji("0x1F4B0") . $balance . ", brah");
            } else {
                Telegram::talk($this->Message->Chat->id, "You don't seem to have a " . COIN_CURRENCY_NAME . " account, brah. That's weird...");
            }
        }
    }
}