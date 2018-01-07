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

class whoami extends Command
{
    public function main()
    {
        $user = $this->Message->User;

        $out = "`User ` *$user->user_id*"
            . "\n`Username ` *$user->user_name*"
            . "\n`First name ` *$user->first_name*"
            . "\n`Last name ` *$user->last_name*"
            . "\n`Balance ` *$user->balance*"
            . "\n`Level ` *$user->level*";

        Telegram::talk($this->Message->Chat->id, $out);
        return true;
    }
}