<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 13/11/2015
 * Time: 10:03 AM
 */

namespace GroupBot\Enums;
use SplEnum;

class ChatType extends SplEnum
{
    const __default = self::Individual;

    const Individual = 1;
    const Group = 2;
    const SuperGroup = 3;
    const Channel = 4;
}