<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 7:01 PM
 */

namespace GroupBot\Brains\Casinowar\Enums;


class PlayerState extends \GroupBot\Brains\CardGame\Enums\PlayerState
{
    const Lose = 3;
    const Win = 4;
    const Draw = 5;
    const Surrender = 6;
    const War = 7;
    const SurrenderForced = 8;
    const WarVictory = 9;
}