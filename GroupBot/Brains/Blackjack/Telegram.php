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
    public static function getResponse(\PDO $db, Message $message, PlayerMove $move, $bet = NULL)
    {
        $Blackjack = new Blackjack($db, $message->User, $message->Chat, $move, $bet);
        if ($Blackjack->Talk->areMessages()) {
            $keyboard = $Blackjack->Talk->getKeyboard();

            if ($message->isCallback()) \GroupBot\Telegram::edit_inline_message($message->Chat->id, $message->message_id, $Blackjack->Talk->getMessages(), $keyboard);
            else \GroupBot\Telegram::talk_inline_keyboard($message->Chat->id, $Blackjack->Talk->getMessages(), $keyboard);

            return true;
        }
        return false;
    }
}