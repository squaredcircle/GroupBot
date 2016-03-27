<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:30 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Coin\Money\Events;
use GroupBot\Brains\Level\Level;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class i_income extends Command
{
    public function i_income()
    {
        $Events = new Events($this->db);
        if ($Events->addIncome($this->Message->User)) {
            Telegram::talk($this->Message->Chat->id, emoji(0x1F4B8) . $this->Message->User->getNameLevelAndTitle() . ", you received your daily allowance of " . Level::getDailyAllowance($this->Message->User->level) . ". You now have `" . ($this->Message->User->getBalance() + COIN_DAILY_INCOME) . "` coin.");
        } else {
            Telegram::talk($this->Message->Chat->id, emoji(0x1F44E) . " You've already received your allowance today!");
        }
    }
}