<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:21 AM
 */
namespace GroupBot\Command;

use GroupBot\Telegram;
use GroupBot\Types\Command;

class speak extends Command
{
    public function main()
    {
        $dir = "/var/www/html/bot";
        $file = \GroupBot\Brains\Speak::createAudioFile($this->getAllParams());
        Telegram::sendVoice($this->Message->Chat->id, "$dir/speech/$file");
    }
}