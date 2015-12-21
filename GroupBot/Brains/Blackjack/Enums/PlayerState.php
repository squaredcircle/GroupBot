<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 7:01 PM
 */

namespace GroupBot\Brains\Blackjack\Enums;


class PlayerState extends \SplEnum
{
    const Join = 1;
    const Stand = 2;
    const Hit = 3;
    const Bust = 4;
    const TwentyOne = 5;
    const BlackJack = 6;
    const Surrender = 7;
    const Dealer = 8;
}