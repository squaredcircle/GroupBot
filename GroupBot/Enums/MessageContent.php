<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 13/11/2015
 * Time: 10:03 AM
 */

namespace GroupBot\Enums;

enum MessageContent: int
{
    //case __default = self::Text;

    case Text = 1;
    case Audio = 2;
    case Document = 3;
    case Photo = 4;
    case Sticker = 5;
    case Video = 6;
    case Voice = 7;
    case Contact = 8;
    case Location = 9;
    case Unknown = 10;
}