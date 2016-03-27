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

class l_level extends Command
{
    public function l_level()
    {
        $Level = new Level();
        $level = $Level->SQL->get_level($this->Message->User->id);
        $title = $Level->getTitle($level);
        $price = $Level->getLevelPrice($level+1);

        $out = emoji(0x1F4EF) . "Greetings *" . $this->Message->User->first_name . "*, the *Level $level $title*."
                . "\nYou may rise to level " . ($level + 1) . " for a price of $price Coin."
                . "\nPlease use /buylevel to do this.";

        Telegram::talk($this->Message->Chat->id, $out);
    }
}