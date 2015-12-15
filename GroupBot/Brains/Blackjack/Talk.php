<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 14/12/2015
 * Time: 10:26 PM
 */

namespace GroupBot\Brains\Blackjack;


use GroupBot\Brains\Blackjack\Enums\PlayerState;
use GroupBot\Brains\Blackjack\Types\Game;
use GroupBot\Brains\Blackjack\Types\Player;

class Talk
{
    private $user_name;
    private $Messages = '';

    public function __construct($user_name)
    {
        $this->user_name = $user_name;
    }

    private function addMessage($message)
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

    public function join_game()
    {
        $this->addMessage($this->user_name . " has joined the game.");
    }

    public function start_game(Game $Game)
    {
        $this->addMessage("The game begins with " . $Game->getNumberOfPlayers() . " players.");
        $this->addMessage("The dealer draws " . $Game->Dealer->Hand->getHandString());
        foreach ($Game->Players as $Player) {
            $this->addMessage($Player->user_name . " has " . $Player->Hand->getHandString());
        }
        if ($Game->getNumberOfPlayers() > 1) {
            $this->addMessage($Game->getCurrentPlayer()->user_name . " goes first.");
        } else {
            $this->addMessage("Please place your move.");
        }
    }

    public function stand()
    {
        $this->addMessage($this->user_name . " stands.");
    }

    public function blackjack()
    {
        $this->addMessage($this->user_name . " has blackjack!");
    }

    public function hit(Player $Player)
    {
        if ($Player->State == PlayerState::Hit) {
            $this->addMessage($this->user_name . " hits.");
        } elseif ($Player->State == PlayerState::Bust) {
            $this->addMessage($this->user_name . " is bust.");
        }
        $out = $this->user_name . "'s cards: " . $Player->Hand->getHandString();
        $this->addMessage($out);
    }

    public function next_turn(Player $Player)
    {
        $this->addMessage("It is now " . $Player->user_name . "'s turn.");
    }

    public function dealer_done(Game $Game, Player $Dealer)
    {
        if ($Game->getNumberOfPlayers() > 1) {
            $this->addMessage('All players have stood or are bust. The dealer draws cards:');
            $this->addMessage($Dealer->Hand->getHandString());
        } else {
            $this->addMessage('The dealer draws cards ' . $Dealer->Hand->getHandString());
        }

        if ($Dealer->State == PlayerState::Bust) {
            $this->addMessage('The dealer is bust.');
        } elseif ($Dealer->State == PlayerState::Stand) {
            $this->addMessage('The dealer stands.');
        }
    }

    public function player_result(Player $Player, $multiplier)
    {
        if ($multiplier > 0) {
            $this->addMessage($Player->user_name . " wins " . $multiplier . "x their bet.");
        } elseif ($multiplier == 0) {
            $this->addMessage($Player->user_name . " regains their bet.");
        } elseif ($multiplier < 0) {
            $this->addMessage($Player->user_name . " looses their bet.");
        }
    }

}