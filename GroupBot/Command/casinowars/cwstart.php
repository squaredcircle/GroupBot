<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:14 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Casinowar\Enums\PlayerMove;
use GroupBot\Brains\Casinowar\Telegram;
use GroupBot\Types\Command;

class c_cwstart extends Command
{
    public function c_cwstart()
    {
        $bet = $this->isParam() ? $this->getAllParams() : 0;
        $Move = new PlayerMove(PlayerMove::StartGame);
        return Telegram::getResponse($this->db, $this->Message, $Move, $bet);
    }
}