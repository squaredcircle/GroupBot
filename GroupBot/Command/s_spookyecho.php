<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:00 AM
 */
namespace GroupBot\Command;

use GroupBot\Enums\ChatType;
use GroupBot\Types\Command;

class s_spookyecho extends Command
{
    public function s_spookyecho()
    {
        if ($this->Message->Chat->type == ChatType::Group)
            $this->Telegram->talk($this->Message->Chat->id, ">not understanding spookyecho");
        elseif (strlen($this->Message->text) == 0)
            $this->Telegram->talk($this->Message->Chat->id, ">still not understanding spookyecho");
        else
            $this->Telegram->talk('-19315940', $this->Message->text);
    }
}