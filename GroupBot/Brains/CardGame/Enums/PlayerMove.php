<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 7:01 PM
 */

namespace GroupBot\Brains\CardGame\Enums;


abstract enum PlayerMove
{
    case JoinGame = 1;
    case StartGame = 2;
    case QuitGame = 3;
    case DefaultMove = 4;
}