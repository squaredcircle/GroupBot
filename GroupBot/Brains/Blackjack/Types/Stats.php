<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 15/02/2016
 * Time: 11:00 AM
 */

namespace GroupBot\Brains\Blackjack\Types;


class Stats extends \GroupBot\Brains\CardGame\Types\Stats
{
    public $hits, $stands, $blackjacks, $splits, $doubledowns, $surrenders;
}