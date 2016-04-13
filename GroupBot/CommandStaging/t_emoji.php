<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:38 AM
 */
namespace GroupBot\Command;

use GroupBot\Telegram;
use GroupBot\Types\Command;

class t_emoji extends Command
{
    public function t_emoji()
    {
        $count = ($this->Message->isText() && is_numeric($this->Message->text))
            ? intval($this->Message->text) : 1;

        Telegram::talk($this->Message->Chat->id, str_repeat(randomEmoji(), $count));
    }
}