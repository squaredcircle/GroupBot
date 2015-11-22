<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:29 AM
 */
namespace GroupBot\Command;

use GroupBot\Types\Command;

class i_isaaccoin extends Command
{
    public function i_isaaccoin()
    {
        $this->Telegram->talk($this->Message->Chat->id, "http://v5.crazyserver.net.au/coin");
    }
}