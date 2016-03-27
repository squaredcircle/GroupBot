<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:27 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Query;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class i_check extends Command
{
    public function i_check()
    {
        if ($this->isParam()) {
            $user = Query::getUserMatchingStringOrErrorMessage($this->db, $this->Message->Chat, $this->getParam());

            if (is_string($user)) {
                Telegram::talk($this->Message->Chat->id, $user);
                return false;
            }
            Telegram::talk($this->Message->Chat->id, $user->getNameLevelAndTitle() . " has " . emoji("0x1F4B0") . $user->getBalance() . ", brah");
        } else {
            Telegram::talk($this->Message->Chat->id, "You've got " . emoji("0x1F4B0") . $this->Message->User->getBalance() . ", brah");
        }
        return true;
    }
}