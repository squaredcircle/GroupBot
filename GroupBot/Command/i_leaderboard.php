<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:14 AM
 */
namespace GroupBot\Command;

use GroupBot\Base\Telegram;
use GroupBot\Brains\Coin\Coin;
use GroupBot\Types\Command;

class i_leaderboard extends Command
{
    public function i_leaderboard()
    {
        $Coin = new Coin();
        $Leaderboard = $Coin->getLeaderboard();
        Telegram::talk($this->Message->Chat->id, $Leaderboard->getTextLeaderboard());
    }
}