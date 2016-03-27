<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:12 AM
 */
namespace GroupBot\Command;

use GroupBot\Telegram;
use GroupBot\Types\Command;

class t_rkeyboard extends Command
{
    public function t_rkeyboard()
    {
        $text = "tada";
        Telegram::talk_hide_keyboard($this->Message->Chat->id, $text);
    }
}