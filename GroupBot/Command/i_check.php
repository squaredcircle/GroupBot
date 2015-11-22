<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:27 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Coin;
use GroupBot\Types\Command;

class i_check extends Command
{
    public function i_check()
    {
        $ic = new Coin();

        if ($this->isParam()) {
            $balance = $ic->getBalanceByUserName($this->Message->text);
            if ($balance >= 0) {
                $this->Telegram->talk($this->Message->Chat->id, $this->Message->text . " has " . emoji("0x1F4B0") . $balance . ", brah");
            } else {
                $this->Telegram->talk($this->Message->Chat->id, emoji("0x1F44E") . " " . $this->Message->text . " doesn't have an Isaac Coin account...");
            }
        } else {
            $balance = $ic->getBalanceByUserId($this->Message->User->id);
            if ($balance >= 0) {
                $this->Telegram->talk($this->Message->Chat->id, "You've got "  . emoji("0x1F4B0") . $balance . ", brah");
            } else {
                $this->Telegram->talk($this->Message->Chat->id, "You gotta /link your account first brah...");
            }
        }
    }
}