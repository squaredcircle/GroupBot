<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 25/04/2016
 * Time: 3:05 PM
 */

namespace GroupBot\Brains\Minesweeper\Types;


use GroupBot\Brains\Minesweeper\Enums\GameState;

class Game
{
    /** @var  Board */
    public $board;

    /** @var  integer */
    public $chat_id;

    /** @var  integer */
    public $user_id;

    /** @var  integer */
    public $game_id;
    
    /** @var  GameState */
    public $state;

    public function __construct(Board $board, $chat_id, $game_id)
    {
        $this->board = $board;
        $this->chat_id = $chat_id;
        $this->game_id = $game_id;
        $this->state = new GameState(GameState::Reveal);
    }
}