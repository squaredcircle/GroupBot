<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 7:01 PM
 */

namespace GroupBot\Brains\Blackjack\Enums;


class PlayerState extends \GroupBot\Brains\CardGame\Enums\PlayerState
{
    const Stand = 3;
    const Hit = 4;
    const Bust = 5;
    const TwentyOne = 6;
    const BlackJack = 7;
    const Surrender = 8;
}
