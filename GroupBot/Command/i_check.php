<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:27 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Coin\Coin;
use GroupBot\Types\Command;

class i_check extends Command
{
    public function i_check()
    {
        $Coin = new Coin();

        if ($this->isParam()) {
            if ($balance = round($Coin->SQL->GetUserByName($this->Message->text)->balance,2)) {
                $this->Telegram->talk($this->Message->Chat->id, "*" . $this->Message->text . "* has " . emoji("0x1F4B0") . $balance . ", brah");
            } else {
                $this->Telegram->talk($this->Message->Chat->id, emoji("0x1F44E") . " Can't find " . $this->Message->text . " on record, brah");
            }
        } else {
            $balance = round($Coin->SQL->GetUserById($this->Message->User->id)->balance,2);
            if ($balance >= 0) {
                $this->Telegram->talk($this->Message->Chat->id, "You've got " . emoji("0x1F4B0") . $balance . ", brah");
            } else {
                $this->Telegram->talk($this->Message->Chat->id, "You gotta /link your account first brah...");
            }
        }
    }
}