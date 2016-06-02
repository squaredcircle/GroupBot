<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:38 AM
 */
namespace GroupBot\Command\misc;

use GroupBot\Telegram;
use GroupBot\Types\Command;

class london extends Command
{
    public function main()
    {
        $word = strlen($this->getAllParams()) > 10 ? substr($this->getAllParams(), 10) : $this->getAllParams();
        $word = strtoupper($word);

        $out = implode(' ', str_split($word));

        foreach (str_split(substr($word,1)) as $char) {
            $out .= "\n$char";
        }

        Telegram::talk($this->Message->Chat->id, "*$out*");
    }
}