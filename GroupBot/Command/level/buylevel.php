<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:38 AM
 */
namespace GroupBot\Command\level;

use GroupBot\Telegram;
use GroupBot\Brains\Level\Level;
use GroupBot\Types\Command;

class buylevel extends Command
{
    public function main()
    {
        $Level = new Level();
        $user = $this->Message->User;
        $greetings = array("Arise", "Congratulations");

        if ($Level->buyLevel($user, $this->db)) {
            $out = emoji(0x1F4EF) . " " . $greetings[mt_rand(0, count($greetings) - 1)] . " *" . $user->first_name . "*, you are now a *Level ". $user->level . " " . $user->getTitle() . "*!"
                . "\nYou may rise to Level " . ($user->level + 1) . " for a price of " . $Level->getLevelPrice($user->level+1) . " Coin.";
        } else {
            $out = emoji(0x1F44E) . " Sorry, you need " . $Level->getLevelPrice($user->level+1) . " Coin to rise to Level " . ($user->level+1) . ".";
        }

        Telegram::talk($this->Message->Chat->id, $out);
    }
}