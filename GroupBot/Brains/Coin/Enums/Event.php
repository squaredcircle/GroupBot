<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 20/11/2015
 * Time: 2:43 PM
 */

namespace GroupBot\Brains\Coin\Enums;


class Event extends \SplEnum
{
    const __default = self::AllTax;

    const AllTax = 1;
    const WealthyTax = 2;
    const PoorTax = 3;
    const RedistributeTax = 4;
    const RedistributeWealthiest = 5;
    const IncreaseValue = 6;
    const DecreaseValue = 7;
    const RandomBonuses = 8;
    const WealthyBonuses = 9;
    const PoorBonuses = 10;
}