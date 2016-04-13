<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:14 AM
 */
namespace GroupBot\Command\blackjack;

use GroupBot\Brains\Blackjack\Enums\PlayerMove;
use GroupBot\Brains\Blackjack\Telegram;
use GroupBot\Types\Command;

class bjstart extends Command
{
    public function main()
    {
        $bet = $this->isParam() ? $this->getAllParams() : 0;
        $Move = new PlayerMove(PlayerMove::StartGame);
        return Telegram::getResponse($this->db, $this->Message, $Move, $bet);
    }
}