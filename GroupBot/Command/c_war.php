<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:14 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Casinowar\Telegram;
use GroupBot\Brains\Casinowar\Enums\PlayerMove;
use GroupBot\Types\Command;

class c_war extends Command
{
    public function c_war()
    {
        $Move = new PlayerMove(PlayerMove::War);
        return Telegram::getResponse($this->db, $this->Message, $Move);
    }
}