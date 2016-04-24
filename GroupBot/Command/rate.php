<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 11:17 PM
 */
namespace GroupBot\Command;

use GroupBot\Libraries\Dictionary;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class rate extends Command
{
    public function main()
    {
        $Dictionary = new Dictionary();
        Telegram::talk($this->Message->Chat->id, $Dictionary->ratings[mt_rand(0,10)]);
    }
}