<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:14 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Blackjack\Blackjack;
use GroupBot\Brains\Blackjack\Enums\PlayerMove;
use GroupBot\Types\Command;

class b_hit extends Command
{
    public function b_hit()
    {
        $Move = new PlayerMove(PlayerMove::Hit);

        $Blackjack = new Blackjack($this->Message->User, $this->Message->Chat->id, $Move, NULL);
        if ($Blackjack->Talk->areMessages()) {
            $this->Telegram->talk($this->Message->Chat->id, $Blackjack->Talk->getMessages());
            return true;
        }
        return false;
    }
}