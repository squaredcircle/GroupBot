<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:38 AM
 */
namespace GroupBot\Command;

use GroupBot\Telegram;
use GroupBot\Brains\Level\Level;
use GroupBot\Types\Command;

class l_buylevel extends Command
{
    public function l_buylevel()
    {
        $Level = new Level();
        $level = $Level->SQL->get_level($this->Message->User->id);
        $greetings = array("Arise", "Congratulations");

        if ($Level->buyLevel($this->Message->User->id)) {
            $out = emoji(0x1F4EF) . $greetings[mt_rand(0, count($greetings) - 1)] . " *" . $this->Message->User->first_name . "*, you are now a *Level ". ($level + 1) . " " . $Level->getTitle($level+1) . "*!"
                . "\nYou may rise to Level " . ($level + 2) . " for a price of " . $Level->getLevelPrice($level+2) . " Coin.";
        } else {
            $out = emoji(0x1F44E) . "Sorry, you need " . $Level->getLevelPrice($level+1) . " Coin to rise to Level " . ($level+1) . ".";
        }

        Telegram::talk($this->Message->Chat->id, $out);
    }
}