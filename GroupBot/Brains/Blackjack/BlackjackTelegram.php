<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 17/01/2016
 * Time: 2:10 PM
 */

namespace GroupBot\Brains\Blackjack;


use GroupBot\Base\Telegram;
use GroupBot\Brains\Blackjack\Enums\PlayerMove;
use GroupBot\Types\Message;

class BlackjackTelegram
{
    public static function getResponse(Message $message, PlayerMove $move, $bet = NULL)
    {
        $Blackjack = new Blackjack($message->User, $message->Chat->id, $move, $bet);
        if ($Blackjack->Talk->areMessages()) {
            $keyboard = $Blackjack->Talk->getKeyboard();
            if ($keyboard) {
                Telegram::reply_keyboard($message->Chat->id, $Blackjack->Talk->getMessages(), $message->message_id, $keyboard);
            } else {
                Telegram::talk_hide_keyboard($message->Chat->id, $Blackjack->Talk->getMessages());
            }
            return true;
        }
        return false;
    }
}