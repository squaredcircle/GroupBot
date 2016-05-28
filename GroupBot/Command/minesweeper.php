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
    /** @var  \GroupBot\Brains\Minesweeper\Minesweeper */
    private $minesweeper;
    private $out;
    private $keyboard;

    private $state;

    public function display()
    {
        if (strcmp($this->state, 'surrender') === 0)
        {
            $this->out = "You surrendered. Game over!";
            $this->keyboard = $this->minesweeper->getBoard(true);
            $this->keyboard[] =
                [
                    [
                        'text' => emoji(0x21A9) . " Play again",
                        'callback_data' => "/minesweeper"
                    ]
                ];
        }
        elseif (strcmp($this->state, 'lose') === 0)
        {
            $this->out = "Boom! Game over.";
            $this->keyboard = $this->minesweeper->getBoard(true);
            $this->keyboard[] =
                [
                    [
                        'text' => emoji(0x21A9) . " Play again",
                        'callback_data' => "/minesweeper"
                    ]
                ];
        }
        else
        {
            $this->out = "Click mode: *uncover mine*";
            $this->keyboard = $this->minesweeper->getBoard();
            $this->keyboard[] =
                [
                    [
                        'text' => emoji(0x1F6A9) . " Toggle Flags",
                        'callback_data' => "/minesweeper flags"
                    ],
                    [
                        'text' => emoji(0x1F6AA) . " Surrender",
                        'callback_data' => "/minesweeper surrender"
                    ]
                ];
        }
    }

    public function parseCommand($command, $parameter = null)
    {
        switch ($command) {
            case 'surrender':
                $this->state = 'surrender';
                $this->display();
                $this->minesweeper->endGame();
                break;
            case 'flags':
                $this->state = 'flags';
                break;
            case 'flag':
                $coords = explode(',', $parameter);
                if (count($coords) == 2)
                    $this->minesweeper->flagTile(intval($coords[0]), intval($coords[1]));
                return $this->display();
                break;
            case 'reveal':
                $coords = explode(',', $parameter);
                if (count($coords) == 2)
                    $this->minesweeper->revealTile(intval($coords[0]), intval($coords[1]));
                return $this->display();
                break;
        }
        return false;
    }

    public function main()
    {

        $this->minesweeper = new \GroupBot\Brains\Minesweeper\Minesweeper($this->db, $this->Message->Chat);
//
//        Telegram::talk($this->Message->Chat->id, 'nmui');
//        return true;

        $this->out = '';

        if ($this->isParam()) {
            if ($this->noParams() == 2) {
                $this->parseCommand($this->getParam(), $this->getParam(1));
            } elseif ($this->noParams() == 1) {
                $this->parseCommand($this->getParam());
            }
        } else {
            $this->display();
        }

        if ($this->Message->isCallback())
            Telegram::edit_inline_message($this->Message->Chat->id, $this->Message->message_id, $this->out, $this->keyboard);
        else Telegram::talk_inline_keyboard($this->Message->Chat->id, $this->out, $this->keyboard);
        return true;
    }
}
