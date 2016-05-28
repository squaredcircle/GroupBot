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

class getadmins extends Command
{
    public function main()
    {
        $admins = Telegram::getChatAdministrators($this->Message->Chat->id);

        Telegram::talk($this->Message->Chat->id, print_r($admins, true));
        return true;
    }
}