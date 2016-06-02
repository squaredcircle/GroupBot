<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:38 AM
 */
namespace GroupBot\Command\misc;

use GroupBot\Telegram;
use GroupBot\Types\Command;

class emoji extends Command
{
    public function main()
    {
        $count = ($this->isParam() && is_numeric($this->getParam()))
            ? intval($this->getParam()) : 1;

        if ($count < 1) $count = 1;
        if ($count > 4096) $count = 4096;

        $out = '';
        for ($i = 0; $i < $count; $i++) $out .= randomEmoji();

        Telegram::talk($this->Message->Chat->id, $out);
    }
}
