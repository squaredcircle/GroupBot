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

class c_surrender extends Command
{
    public function c_surrender()
    {
        $Move = new PlayerMove(PlayerMove::Surrender);
        return Telegram::getResponse($this->db, $this->Message, $Move);
    }
}