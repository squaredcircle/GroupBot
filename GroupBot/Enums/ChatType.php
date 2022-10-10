<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 13/11/2015
 * Time: 10:03 AM
 */

namespace GroupBot\Enums;

enum ChatType: int
{
    // case __default = self::Individual;

    case Individual = 1;
    case Group = 2;
    case SuperGroup = 3;
    case Channel = 4;
}