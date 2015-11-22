<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:09 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Zalgo;
use GroupBot\Types\Command;

class q_zalgomin extends Command
{
    public function q_zalgomin()
    {
        $zalgo = new Zalgo('twitter');
        $out = $zalgo->speak($this->Message->text);

        $this->Telegram->talk($this->Message->Chat->id, $out);
    }
}