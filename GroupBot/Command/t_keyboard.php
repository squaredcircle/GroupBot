<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:12 AM
 */
namespace GroupBot\Command;

use GroupBot\Types\Command;

class t_keyboard extends Command
{
    public function t_keyboard()
    {
        $text = "here you go fam";
        $keyboard = array(
            ["/roll", "/check", "/banana"]
        );
        $this->Telegram->reply_keyboard($this->Message->Chat->id, $text, $this->Message->message_id, $keyboard);
    }
}