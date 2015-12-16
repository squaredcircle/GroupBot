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
use GroupBot\Brains\Coin;
use GroupBot\Enums\ChatType;
use GroupBot\Types\Command;

class b_blackjack extends Command
{
    public function b_blackjack()
    {
        if ($this->Message->Chat->type == ChatType::Individual) {
            $Move = new PlayerMove(PlayerMove::StartGame);
        } else {
            $Move = new PlayerMove(PlayerMove::JoinGame);
        }

        $bet = 0;
        if ($this->isParam() && is_numeric($this->getParam())) {
            $ic = new Coin();
            $balance = $ic->getBalanceByUserId($this->Message->User->id);
            $bet = round($this->getParam(),2);

            if ($bet >= round($balance,2)) {
                $this->Telegram->talk($this->Message->Chat->id, "you don't have that much coin, brah");
                return false;
            }
        }

        $Blackjack = new Blackjack($this->Message->User, $this->Message->Chat->id, $Move, $bet);
        if ($Blackjack->Talk->areMessages()) {
            $this->Telegram->talk($this->Message->Chat->id, $Blackjack->Talk->getMessages());
            return true;
        }
        return false;
    }
}