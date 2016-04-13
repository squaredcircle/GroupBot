<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:22 AM
 */
namespace GroupBot\Command;

use GroupBot\Telegram;
use GroupBot\Enums\ChatType;
use GroupBot\Types\Command;

class s_spookyperson extends Command
{
    public function s_spookyperson()
    {
        if ($this->Message->Chat->type == ChatType::Group)
            Telegram::talk($this->Message->Chat->id, ">not understanding spookyperson");
        elseif (strlen($this->Message->text) == 0)
            Telegram::talk($this->Message->Chat->id, ">still not understanding spookyperson");
        else
            Telegram::talk('-19315940', person($this->Message->text, true));
    }
}