<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 11:17 PM
 */
namespace GroupBot\Command;

use GroupBot\Telegram;
use GroupBot\Types\Command;

class t_rate extends Command
{
    public function t_rate()
    {
        require(__DIR__ . '/../libraries/Dictionary.php');
        Telegram::talk($this->Message->Chat->id, $ratings[mt_rand(0,10)]);
    }
}