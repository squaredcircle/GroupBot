<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:21 AM
 */
namespace GroupBot\Command;

use GroupBot\Base\Telegram;
use GroupBot\Types\Command;

class q_person extends Command
{
    public function q_person()
    {
        Telegram::talk($this->Message->Chat->id, person($this->Message->text, true));
    }
}