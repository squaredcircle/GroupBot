<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:21 AM
 */
namespace GroupBot\Command;

use GroupBot\Telegram;
use GroupBot\Types\Command;

class suicide extends Command
{
    public function main()
    {
        $out = Telegram::kick2($this->Message->Chat->id, $this->Message->User->user_id);

        Telegram::talkForced($this->Message->Chat->id, print_r($out, true));
    }
}