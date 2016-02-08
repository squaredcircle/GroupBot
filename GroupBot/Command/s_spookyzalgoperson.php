<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:37 AM
 */
namespace GroupBot\Command;

use GroupBot\Base\Telegram;
use GroupBot\Brains\Zalgo;
use GroupBot\Enums\ChatType;
use GroupBot\Types\Command;

class s_spookyzalgoperson extends Command
{
    public function s_spookyzalgoperson()
    {
        $zalgo = new Zalgo(NULL);
        $out = $zalgo->speak($this->Message->text);

        $no1 = $zalgo->speak(">not understanding spookyzalgoperson");
        $no2 = $zalgo->speak(">still not understanding spookyzalgoperson");


        if ($this->Message->Chat->type == ChatType::Group)
            Telegram::talk($this->Message->Chat->id, $no1);
        elseif (strlen($this->Message->text) == 0)
            Telegram::talk($this->Message->Chat->id, $no2);
        else
            Telegram::talk('-19315940', person($out, false));
    }
}