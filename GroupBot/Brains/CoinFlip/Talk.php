<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/09/2016
 * Time: 11:22 PM
 */

namespace GroupBot\Brains\CoinFlip;


use GroupBot\Types\User;

class Talk
{
    private $Messages = '';
    private $keyboard = false;

    public function __construct()
    {

    }

    public function addMessage($message)
    {
        $this->Messages .= "\n" . $message;
    }

    public function areMessages()
    {
        return $this->Messages != '';
    }

    public function getMessages()
    {
        return $this->Messages;
    }

    public function getKeyboard()
    {
        if ($this->keyboard) {
            return $this->keyboard;
        }
        return false;
    }

    public function join_game(User $user, $bet)
    {

    }

    public function player_choose(Player $player)
    {

    }

    public function dealer_flip($choice)
    {

    }
}