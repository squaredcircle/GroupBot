<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 13/11/2015
 * Time: 10:03 AM
 */

namespace GroupBot\Enums;

enum MessageType: int
{
    //case __default = self::Regular;

    case Regular = 1;
    case Reply = 2;
    case Forward = 3;
    case NewChatParticipant = 4;
    case LeftChatParticipant = 5;
    case NewChatTitle = 6;
    case NewChatPhoto = 7;
    case DeleteChatPhoto = 8;
    case GroupChatCreated = 9;
    case SuperGroupChatCreated = 10;
    case ChannelChatCreated = 11;
}