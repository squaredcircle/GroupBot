<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 7:01 PM
 */

namespace GroupBot\Brains\Minesweeper\Enums;


class GameState extends \SplEnum
{
    const Reveal = 1;
    const Flag = 2;
    const Win = 3;
    const Lose = 4;
}