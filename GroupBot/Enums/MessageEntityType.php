<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 14/05/2016
 * Time: 12:49 AM
 */

namespace GroupBot\Enums;

enum MessageEntityType
{
    case __default = self::mention;

    case mention = 1;
    case hashtag = 2;
    case bot_command = 3;
    case url = 4;
    case email = 5;
    case bold = 6;
    case italic = 7;
    case code = 8;
    case pre = 9;
    case text_link = 10;
}