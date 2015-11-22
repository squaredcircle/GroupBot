<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 13/11/2015
 * Time: 10:03 AM
 */

namespace GroupBot\Enums;

use SplEnum;

class MessageType extends SplEnum
{
    const __default = self::Regular;

    const Regular = 1;
    const Reply = 2;
    const Forward = 3;
    const NewChatParticipant = 4;
    const LeftChatParticipant = 5;
    const NewChatTitle = 6;
    const NewChatPhoto = 7;
    const DeleteChatPhoto = 8;
    const GroupChatCreated = 9;
}