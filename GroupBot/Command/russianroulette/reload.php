<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:07 AM
 */
namespace GroupBot\Command\russianroulette;

use GroupBot\Brains\RussianRoulette\RussianRoulette;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class reload extends Command
{
    public function main()
    {
        $RussianRoulette = new RussianRoulette($this->db, $this->Message->Chat->id, $this->Message->User->user_id);

        $RussianRoulette->reload();
        $out = emoji(0x1F52B) . " `Revolver reloaded.`"
            . "\n"
            . "\nThere are *six* chambers, and *one* bullet."
            . "\nUse /trigger to play.";

        Telegram::talk($this->Message->Chat->id, $out);
    }
}