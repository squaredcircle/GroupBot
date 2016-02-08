<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 6/11/2015
 * Time: 2:59 PM
 */
namespace GroupBot\Command;

use GroupBot\Base\Telegram;
use GroupBot\Types\Command;

class q_echo extends Command
{
    public function q_echo()
    {
        if ($this->Message->isText())
            Telegram::talk($this->Message->Chat->id, ">not understanding echo");
        else
            Telegram::talk($this->Message->Chat->id, $this->Message->text);
    }
}