<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 7:01 PM
 */

namespace GroupBot\Brains\CardGame\Enums;


abstract class PlayerMove extends \SplEnum
{
    const JoinGame = 1;
    const StartGame = 2;
    const QuitGame = 3;
    const DefaultMove = 4;
}