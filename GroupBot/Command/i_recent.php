<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:32 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Coin\Coin;
use GroupBot\Types\Command;

class i_recent extends Command
{
    public function i_recent()
    {
        $Coin = new Coin();
        $RecentLogs = $Coin->getRecentLogs();
        $this->Telegram->talk($this->Message->Chat->id, $RecentLogs->getRecentLogsText());
    }
}