<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:06 AM
 */
namespace GroupBot\Command;

use GroupBot\Types\Command;
use GroupBot\Brains\Blackjack;

class t_blackjack extends Command
{
    public function t_blackjack()
    {
//        $bj = new Blackjack($this->Message->User->id, $this->Message->Chat->id, $this->Message->text);
//
//        if ($bj->game_loaded)
//            $this->Telegram->talk($this->Message->Chat->id, $bj->gameStatus());
//        else
//            $this->Telegram->talk($this->Message->Chat->id, "okay brah.\n" . emoji(0x1F449) . " to play the dealer, use /blackjack dealer\n" . emoji(0x1F449) . " to play others, everybody playing use /blackjack join");
    }
}