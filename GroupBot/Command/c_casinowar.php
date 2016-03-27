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
use GroupBot\Brains\Coin;
use GroupBot\Enums\ChatType;
use GroupBot\Types\Command;

class c_casinowar extends Command
{
    public function c_casinowar()
    {
        if ($this->Message->Chat->type == ChatType::Individual) {
            $Move = new PlayerMove(PlayerMove::StartGame);
        } else {
            $Move = new PlayerMove(PlayerMove::JoinGame);
        }

        $bet = $this->isParam() ? $this->getAllParams() : 0;
        return Telegram::getResponse($this->db, $this->Message, $Move, $bet);
    }
}