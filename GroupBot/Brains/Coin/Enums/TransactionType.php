<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 20/11/2015
 * Time: 2:43 PM
 */

namespace GroupBot\Brains\Coin\Enums;


class TransactionType extends \SplEnum
{
    const __default = self::Unspecified;

    const Unspecified = 0;
    const Manual = 1;
    const TransactionTax = 2;

    const BlackjackBet = 10;
    const BlackjackWin = 11;
    const CasinoWarBet = 12;
    const CasinoWarWin = 13;

    const AllTax = 20;
    const WealthyTax = 21;
    const PoorTax = 22;
    const RedistributionTax = 23;
    const RedistributeWealthiest = 24;
    const IncreaseValue = 25;
    const DecreaseValue = 26;
    const RandomBonus = 27;
    const WealthyBonus = 28;
    const PoorBonus = 29;
}