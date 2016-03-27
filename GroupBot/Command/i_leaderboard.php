<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:14 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Query;
use GroupBot\Telegram;
use GroupBot\Types\Command;
use GroupBot\Types\User;

class i_leaderboard extends Command
{
    private $global = false;

    /**
     * @param $str
     * @param array $arr
     * @return bool
     */
    private function contains($str, array $arr)
    {
        foreach ($arr as $a) {
            if (stripos($a, $str) !== false)
                return true;
        }
        return false;
    }

    /**
     * @return User[]|bool
     */
    private function getUsers()
    {
        $chat = $this->Message->Chat;
        $ascending = false;
        $no_users = 10;

        if ($this->isParam()) {
            if ($this->contains($this->getAllParams(), ['global', 'all', 'every'])) {
                $chat = NULL;
                $this->global = true;
            }
            if ($this->contains($this->getAllParams(), ['bottom', 'last'])) {
                $ascending = true;
            }
        }
        return Query::getUsersByMoneyAndLevel($this->db, $chat, true, $ascending, $no_users);
    }

    /**
     * @param User[] $users
     * @return string
     */
    private function getTextLeaderboard($users)
    {
        $out = "";
        $index = 1;

        foreach ($users as $user) {
            $out .= "`" . addOrdinalNumberSuffix($index);
            if ($index == 10) {
                $out .= " `";
            } else {
                $out .= "  `";
            }
            if ($user->user_id == COIN_BANK_ID) $user->level = 99;
            $out .= "_Lvl " . $user->level;
            if ($user->level >= 10) {
                $out .= " _ * ";
            } else {
                $out .= "   _ * ";
            }

            $out .= $user->getName() . "* (" . $user->getBalance() . ")\n";
            $index++;
            if ($index > 10)
                break;
        }

        return $out;
    }

    public function i_leaderboard()
    {
        if ($users = $this->getUsers()) {
            if ($this->global) {
                $out = "*Global* leaderboard:\n";
                $out .= $this->getTextLeaderboard($users);
            }
            else {
                $out = "*Leaderboard for *" . $this->Message->Chat->title . "*:\n";
                $out .= $this->getTextLeaderboard($users);
            }
        } else {
            $out = "Can't find any users, brah";
        }
        Telegram::talk($this->Message->Chat->id, $out);
    }
}