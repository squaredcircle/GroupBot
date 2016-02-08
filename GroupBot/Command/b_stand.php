<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:14 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Blackjack\BlackjackTelegram;
use GroupBot\Brains\Blackjack\Enums\PlayerMove;
use GroupBot\Types\Command;

class b_stand extends Command
{
    public function b_stand()
    {
        $Move = new PlayerMove(PlayerMove::Stand);
        return BlackjackTelegram::getResponse($this->Message, $Move);
    }
}