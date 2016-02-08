<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:00 AM
 */
namespace GroupBot\Command;

use GroupBot\Base\Telegram;
use GroupBot\Enums\ChatType;
use GroupBot\Types\Command;

class s_spookyecho extends Command
{
    public function s_spookyecho()
    {
        if ($this->Message->Chat->type == ChatType::Group)
            Telegram::talk($this->Message->Chat->id, ">not understanding spookyecho");
        elseif (strlen($this->Message->text) == 0)
            Telegram::talk($this->Message->Chat->id, ">still not understanding spookyecho");
        else
            Telegram::talk('-19315940', $this->Message->text);
    }
}