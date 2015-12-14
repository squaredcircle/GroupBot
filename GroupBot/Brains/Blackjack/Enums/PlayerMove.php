<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 7:01 PM
 */

namespace GroupBot\Brains\Blackjack\Enums;


class PlayerMove extends \SplEnum
{
    const JoinGame = 1;
    const StartGame = 2;
    const QuitGame = 3;
    const Hit = 4;
    const Stand = 5;
    const DoubleDown = 6;
    const Split = 7;
    const Surrender = 8;
}