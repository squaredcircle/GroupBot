<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:37 AM
 */
namespace GroupBot\Command;

use GroupBot\Base\Telegram;
use GroupBot\Types\Command;
use GroupBot\Brains\Zalgo;

class q_zalgoperson extends Command
{
    public function q_zalgoperson()
    {
        $zalgo = new Zalgo(NULL);
        $out = $zalgo->speak($this->Message->text);

        Telegram::talk($this->Message->Chat->id, person($out, false));
    }
}