<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:38 AM
 */
namespace GroupBot\Command\level;

use GroupBot\Brains\Query;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class level extends Command
{
    public function main()
    {
        $Level = new \GroupBot\Brains\Level\Level();

        if ($this->isParam())
        {
            $user = Query::getUserMatchingStringOrErrorMessage($this->db, $this->Message->Chat, $this->getParam());
            if (is_string($user))
            {
                $out = $user;
            }
            else
            {
                $out = emoji(0x1F4EF) . " Make way for " . $user->getNameLevelAndTitle() . "!";
            }
        }
        else
        {
            $user = $this->Message->User;
            $out = emoji(0x1F4EF) . " Greetings " . $user->getNameLevelAndTitle() . "."
                . "\nYou may rise to level " . ($user->level + 1) . " for a price of " . $Level->getLevelPrice($user->level + 1) . " Coin."
                . "\nPlease use /buylevel to do this.";
        }

        Telegram::talk($this->Message->Chat->id, $out);
    }
}