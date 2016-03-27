<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 17/01/2016
 * Time: 2:10 PM
 */

namespace GroupBot\Brains\Casinowar;


use GroupBot\Brains\Casinowar\Enums\PlayerMove;
use GroupBot\Types\Message;

class Telegram
{
    public static function getResponse(\PDO $db, Message $message, PlayerMove $move, $bet = NULL)
    {
        $Casinowar = new Casinowar($db, $message->User, $message->Chat->id, $move, $bet);
        if ($Casinowar->Talk->areMessages()) {
            $keyboard = $Casinowar->Talk->getKeyboard();
            if ($keyboard) {
                \GroupBot\Telegram::reply_keyboard($message->Chat->id, $Casinowar->Talk->getMessages(), $message->message_id, $keyboard);
            } else {
                \GroupBot\Telegram::talk_hide_keyboard($message->Chat->id, $Casinowar->Talk->getMessages());
            }
            return true;
        }
        return false;
    }
}