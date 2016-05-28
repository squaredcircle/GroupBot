<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 11:17 PM
 */
namespace GroupBot\Command;


use GroupBot\Telegram;
use GroupBot\Types\Command;

class click extends Command
{
    private $out;
    private $keyboard;

    public function generateKeyboard()
    {
        $height = mt_rand(1,6);
        $width = mt_rand(1,6);
        $button = mt_rand(0, $height * $width - 1);

        $this->keyboard = [];
        for ($i = 0; $i < $height; $i++) {
            $row = [];
            for ($j = 0; $j < $width; $j++) {
                if ($i * $width + $j == $button) {
                    $row[] = [
                        'text' => emoji(0x1F535),
                        'callback_data' => "/click button"
                    ];
                } else {
                    $row[] = [
                        'text' => emoji(0x274C),
                        'callback_data' => "/click wrong"
                    ];
                }
            }
            $this->keyboard[] = $row;
        }
        return true;
    }

    public function main()
    {
        if ($this->Message->isCallback())
        {
            if ($this->isParam() && strcmp($this->getParam(), 'button') === 0) {
                $this->out = emoji(0x1F38C) . " *WINNER!*\n\n*" . $this->Message->User->getName() . "* clicked the button!";
                Telegram::edit_inline_message($this->Message->Chat->id, $this->Message->message_id, $this->out, $this->keyboard);
            }
        }
        else
        {
            $this->out = emoji(0x1F3C1) . " *Click the button to win!!*";
            $this->generateKeyboard();
            Telegram::talk_inline_keyboard($this->Message->Chat->id, $this->out, $this->keyboard);
        }
        return true;
    }
}