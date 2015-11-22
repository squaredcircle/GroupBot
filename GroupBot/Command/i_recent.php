<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:32 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Coin;
use GroupBot\Types\Command;

class i_recent extends Command
{
    public function i_recent()
    {
        $ic = new Coin();
        $ic = $ic->getObject();

        $this->Telegram->talk($this->Message->Chat->id, $ic->recentLogs->getRecentLogsText());
    }
}