<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 13/11/2015
 * Time: 10:03 AM
 */

namespace GroupBot\Enums;

use SplEnum;

class MessageContent extends SplEnum
{
    const __default = self::Text;

    const Text = 1;
    const Audio = 2;
    const Document = 3;
    const Photo = 4;
    const Sticker = 5;
    const Video = 6;
    const Voice = 7;
    const Contact = 8;
    const Location = 9;
    const Unknown = 10;
}