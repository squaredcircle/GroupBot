<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 7:01 PM
 */

namespace GroupBot\Brains\Minesweeper\Enums;


enum GameState
{
    case Reveal = 1;
    case Flag = 2;
    case Win = 3;
    case Lose = 4;
}