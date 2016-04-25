<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:11 AM
 */
namespace GroupBot\Command;

use GroupBot\Telegram;
use GroupBot\Types\Command;

class minesweeper extends Command
{
    private $out;
    private $keyboard;

    public function minesweep()
    {
        $this->out = "Click mode: *uncover mine*";

        $this->keyboard = [];
        for ($i = 0; $i < 13; $i++) {
            $row = [];

            if ($i==12) {
                $row = [
                    [
                        'text' => emoji(0x1F6A9) . " Toggle Flags",
                        'callback_data' => "/minesweeper flags"
                    ],
                    [
                        'text' => emoji(0x1F6AA) . " Surrender",
                        'callback_data' => "/minesweeper surrender"
                    ]
                ];
            } else {
                for ($j = 0; $j < 8; $j++) {
                    $row[] = [
                        'text' => "$j,$i",
                        'callback_data' => "/minesweeper $j,$i"
                    ];
                }
            }
            $this->keyboard[] = $row;
        }
    }

    public function main()
    {
        $this->out = '';

        if (!$this->Message->Chat->isPrivate()) {
            $this->out = "only works in private, sorry fam. blame telegram\ntalk to @Shit3Bot";
            Telegram::talk($this->Message->Chat->id, $this->out);
            return true;
        }

        $this->minesweep();

        if ($this->Message->isCallback())
            Telegram::edit_inline_message($this->Message->Chat->id, $this->Message->message_id, $this->out, $this->keyboard);
        else Telegram::talk_inline_keyboard($this->Message->Chat->id, $this->out, $this->keyboard);
        return true;
    }
}