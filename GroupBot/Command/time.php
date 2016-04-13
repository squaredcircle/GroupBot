<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:07 AM
 */
namespace GroupBot\Command;

use GroupBot\Telegram;
use GroupBot\Types\Command;

class time extends Command
{
    public function main()
    {
        $time = date('g:i A');
        Telegram::talk($this->Message->Chat->id, emoji(0x231A) . " It's *" . $time . "*");
    }
}