<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:14 AM
 */
namespace GroupBot\Command\blackjack;

use GroupBot\Brains\Blackjack\Telegram;
use GroupBot\Brains\Blackjack\Enums\PlayerMove;
use GroupBot\Types\Command;

class doubledown extends Command
{
    public function main()
    {
        $Move = new PlayerMove(PlayerMove::DoubleDown);
        return Telegram::getResponse($this->db, $this->Message, $Move);
    }
}