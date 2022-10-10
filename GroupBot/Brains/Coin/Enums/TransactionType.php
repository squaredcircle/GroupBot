<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 20/11/2015
 * Time: 2:43 PM
 */

namespace GroupBot\Brains\Coin\Enums;


enum TransactionType: int
{
    // case __default = self::Unspecified;

    case Unspecified = 0;
    case Manual = 1;
    case TransactionTax = 2;

    case BlackjackBet = 10;
    case BlackjackWin = 11;
    case CasinoWarBet = 12;
    case CasinoWarWin = 13;

    case AllTax = 20;
    case WealthyTax = 21;
    case PoorTax = 22;
    case RedistributionTax = 23;
    case RedistributeWealthiest = 24;
    case IncreaseValue = 25;
    case DecreaseValue = 26;
    case RandomBonus = 27;
    case WealthyBonus = 28;
    case PoorBonus = 29;

    case DailyIncome = 40;

    case LevelPurchase = 50;

    case Apocalypse = 60;
}