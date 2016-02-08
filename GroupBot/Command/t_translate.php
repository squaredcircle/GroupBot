<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:07 AM
 */
namespace GroupBot\Command;

use GroupBot\Base\Telegram;
use GroupBot\Brains\Translate;
use GroupBot\Types\Command;

class t_translate extends Command
{
    public function t_translate()
    {
        $Translate = new Translate();
        $translation = $Translate->translate($this->Message->text, 'English');
        Telegram::talk($this->Message->Chat->id, "*" . $translation['result'][0] . "*");
    }
}