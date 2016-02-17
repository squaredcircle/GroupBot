<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 17/01/2016
 * Time: 2:10 PM
 */

namespace GroupBot\Brains\Blackjack;


use GroupBot\Brains\Blackjack\Enums\PlayerMove;
use GroupBot\Types\Message;

class Telegram
{
    public static function getResponse(Message $message, PlayerMove $move, $bet = NULL)
    {
        $Blackjack = new Blackjack($message->User, $message->Chat->id, $move, $bet);
        if ($Blackjack->Talk->areMessages()) {
            $keyboard = $Blackjack->Talk->getKeyboard();
            if ($keyboard) {
                \GroupBot\Base\Telegram::reply_keyboard($message->Chat->id, $Blackjack->Talk->getMessages(), $message->message_id, $keyboard);
            } else {
                \GroupBot\Base\Telegram::talk_hide_keyboard($message->Chat->id, $Blackjack->Talk->getMessages());
            }
            return true;
        }
        return false;
    }
}