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

class b_blackjack extends Command
{
    public function b_blackjack()
    {
        if ($this->isParam()) {
            switch ($this->getParam()) {
                case 'join':
                    $Move = new PlayerMove(PlayerMove::JoinGame);
                    break;
                case 'start':
                    $Move = new PlayerMove(PlayerMove::StartGame);
                    break;
                case 'hit':
                    $Move = new PlayerMove(PlayerMove::Hit);
                    break;
                case 'stand':
                    $Move = new PlayerMove(PlayerMove::Stand);
                    break;
                default:
                    $this->Telegram->talk($this->Message->Chat->id, "like this, fam:\n`/blackjack` *join*/*start*/*hit*/*stand*");
                    return false;
            }

            $Blackjack = new Blackjack($this->Message->User, $this->Message->Chat->id, $Move);
            if ($Blackjack->Talk->areMessages()) {
                $this->Telegram->talk($this->Message->Chat->id, $Blackjack->Talk->getMessages());
                return true;
            }
            return false;
        }

        $this->Telegram->talk($this->Message->Chat->id, "like this, fam:\n`/blackjack` *join*/*start*/*hit*/*stand*");
        return false;
    }
}