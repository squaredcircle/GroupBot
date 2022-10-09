<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 13/11/2015
 * Time: 10:03 AM
 */

namespace GroupBot\Brains\Vote\Enums;

enum VoteType
{
    case __default = self::Neutral;

    case Down = -1;
    case Neutral = 0;
    case Up = 1;
}