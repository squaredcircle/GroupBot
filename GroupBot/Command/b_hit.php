<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:14 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Blackjack\Telegram;
use GroupBot\Brains\Blackjack\Enums\PlayerMove;
use GroupBot\Types\Command;

class b_hit extends Command
{
    public function b_hit()
    {
        $Move = new PlayerMove(PlayerMove::Hit);
        return Telegram::getResponse($this->Message, $Move);
    }
}