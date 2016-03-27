<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 13/11/2015
 * Time: 10:03 AM
 */

namespace GroupBot\Brains\Vote\Enums;
use SplEnum;

class VoteType extends SplEnum
{
    const __default = self::Neutral;

    const Down = -1;
    const Neutral = 0;
    const Up = 1;
}