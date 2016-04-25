<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 25/04/2016
 * Time: 3:05 PM
 */

namespace GroupBot\Brains\Minesweeper\Types;


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
}