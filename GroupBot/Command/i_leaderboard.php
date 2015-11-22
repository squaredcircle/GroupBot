<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:14 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Coin;
use GroupBot\Types\Command;

class i_leaderboard extends Command
{
    public function i_leaderboard()
    {
        $ic = new Coin();
        $ic = $ic->getObject();

        $this->Telegram->talk($this->Message->Chat->id, $ic->Leaderboard->getTextLeaderboard());
    }
}