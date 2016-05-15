<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 14/05/2016
 * Time: 12:49 AM
 */

namespace GroupBot\Enums;


class MessageEntityType extends \SplEnum
{
    const __default = self::mention;

    const mention = 1;
    const hashtag = 2;
    const bot_command = 3;
    const url = 4;
    const email = 5;
    const bold = 6;
    const italic = 7;
    const code = 8;
    const pre = 9;
    const text_link = 10;
}