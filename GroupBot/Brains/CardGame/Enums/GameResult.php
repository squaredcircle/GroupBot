<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 7:01 PM
 */

namespace GroupBot\Brains\CardGame\Enums;

enum GameResult
{
    case Win = 1;
    case Loss = 2;
    case Draw = 3;
}