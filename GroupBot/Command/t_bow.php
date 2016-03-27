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

class t_bow extends Command
{
    public function t_bow()
    {
        Telegram::talk($this->Message->Chat->id, "*roaring applause*");
    }
}