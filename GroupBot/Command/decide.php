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

class decide extends Command
{
    public function main()
    {
        if ($this->isParam() && $this->noParams() > 2) {
            $params = explode(' or ', $this->getAllParams());
            if (count($params) > 1) {
                $decision = mt_rand(0, count($params) - 1);
                $out = emoji(0x1F44D) . " *" . $params[$decision] . "*";
                Telegram::talk($this->Message->Chat->id, $out);
                return true;
            }
        }

        if (mt_rand(0, 1)) {
            $out = emoji(0x1F44D) . " *Yes!*";
        } else {
            $out = emoji(0x1F44E) . " *No.*";
        }

        Telegram::talk($this->Message->Chat->id, $out);
        return true;
    }
}