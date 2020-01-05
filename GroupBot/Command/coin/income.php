<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:30 AM
 */
namespace GroupBot\Command\coin;

use GroupBot\Brains\Coin\Money\Events;
use GroupBot\Brains\Level\Level;
use GroupBot\Command\misc\emoji;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class income extends Command
{
    public function main()
    {
        $Events = new Events($this->db);
        if ($Events->addIncome($this->Message->User)) {
            Telegram::talk($this->Message->Chat->id, emoji(0x1F4B8) . " " .  $this->Message->User->getNameLevelAndTitle() . ", you received your daily allowance of `" . Level::getDailyAllowance($this->Message->User->level) . "` coin. You now have `" . $this->Message->User->getBalance() . "` coin.");
        } else {
            if ($Events->Transact->Feedback->isFeedback()) {
                $out = emoji("0x1F4E2") . " " . $Events->Transact->Feedback->getFeedback();
            }
            else {
                $now = new \DateTime();
                $future_date = new \DateTime('tomorrow');
                $interval = $future_date->diff($now);
                $time = $interval->format("*%h hours* and *%i minutes*");

                $out = emoji(0x1F4E2) . " " . $this->Message->User->getNameLevelAndTitle() . "..."
                    . "\n\n" . emoji(0x1F44E) . " You must wait $time until you can add to your collection of `" . $this->Message->User->getBalance() . "` coin.";

                //$out = emoji(0x1F44E) . " You've already received your allowance today!"
                //     . "\nThere's still $time to go until tomorrow";
            }

            Telegram::talk($this->Message->Chat->id, $out);
        }
    }
}