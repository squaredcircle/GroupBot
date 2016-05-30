<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:11 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Minesweeper\Enums\GameState;
use GroupBot\Brains\Minesweeper\Types\Game;
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
        if ($this->minesweeper->game->state == GameState::Lose) $this->state = 'lose';
        if ($this->minesweeper->game->state == GameState::Win) $this->state = 'win';

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
        elseif (strcmp($this->state, 'win') === 0)
        {
            $this->out = "Victory! You uncovered all the non-mined tiles.";
            $this->keyboard = $this->minesweeper->getBoard(true);
            $this->keyboard[] =
                [
                    [
                        'text' => emoji(0x21A9) . " Play again",
                        'callback_data' => "/minesweeper"
                    ]
                ];
        }
        elseif (strcmp($this->state, 'reveal_mode') === 0)
        {
            $this->out = "Click mode: *uncover mine*";
            $this->keyboard = $this->minesweeper->getBoard();
            $this->keyboard[] =
                [
                    [
                        'text' => emoji(0x1F6A9) . " Toggle Flags ON",
                        'callback_data' => "/minesweeper flag_mode"
                    ],
                    [
                        'text' => emoji(0x1F6AA) . " Surrender",
                        'callback_data' => "/minesweeper surrender"
                    ]
                ];
        }
        else
        {
            $this->out = "Click mode: *place flag*";
            $this->keyboard = $this->minesweeper->getBoard();
            $this->keyboard[] =
                [
                    [
                        'text' => emoji(0x1F6A9) . " Toggle Flags OFF",
                        'callback_data' => "/minesweeper reveal_mode"
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
            case 'flag_mode':
                $this->state = 'flag_mode';
                $this->minesweeper->game->state = new GameState(GameState::Flag);
                return $this->display();
                break;
            case 'reveal_mode':
                $this->state = 'reveal_mode';
                $this->minesweeper->game->state = new GameState(GameState::Reveal);
                return $this->display();
                break;
            case 'flag':
                $this->state = 'flag_mode';
                $coords = explode(',', $parameter);
                if (count($coords) == 2)
                    $this->minesweeper->flagTile(intval($coords[0]), intval($coords[1]));
                return $this->display();
                break;
            case 'reveal':
                $this->state = 'reveal_mode';
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
