<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 20/11/2015
 * Time: 2:43 PM
 */

namespace GroupBot\Brains\Coin\Enums;


enum Event
{
    case __default = self::AllTax;

    case AllTax = 1;
    case WealthyTax = 2;
    case PoorTax = 3;
    case RedistributeTax = 4;
    case RedistributeWealthiest = 5;
    case IncreaseValue = 6;
    case DecreaseValue = 7;
    case RandomBonuses = 8;
    case WealthyBonuses = 9;
    case PoorBonuses = 10;
}