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

    public function join_game($bet)
    {
        $out = $this->user_name . " has joined the game";
        if ($bet > 0) {
            $out .= " with a bet of " . $bet . " coin.";
        } else {
            $out .= ".";
        }
        $this->addMessage($out);
        $this->addMessage("Others can also join the game with /blackjack");
        $this->addMessage("You can start the game with /bjstart");
    }

    public function start_game(Game $Game)
    {
        if ($Game->getNumberOfPlayers() > 1) {
            $this->addMessage("The game begins with " . $Game->getNumberOfPlayers() . " players.");
        } else {
            $this->addMessage("You're betting " . $Game->getCurrentPlayer()->bet . " coin.");
        }
        $this->addMessage("The dealer draws " . $Game->Dealer->Hand->getHandString() . " (" . $Game->Dealer->Hand->Value . ")");
        foreach ($Game->Players as $Player) {
            $this->addMessage($Player->user_name . " has " . $Player->Hand->getHandString()  . " (" . $Player->Hand->Value . ")");
        }
        if ($Game->getNumberOfPlayers() > 1) {
            $this->addMessage($Game->getCurrentPlayer()->user_name . " goes first.");
        } else {
            $this->addMessage("Please place your move.");
        }
        $this->addMessage("You can /hit or /stand");
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
        $out = $this->user_name . "'s cards: " . $Player->Hand->getHandString() . " (" . $Player->Hand->Value . ")";
        $this->addMessage($out);
    }

    public function next_turn(Game $Game)
    {
        if ($Game->getNumberOfPlayers() > 1) {
            $this->addMessage("It is now " . $Game->getCurrentPlayer()->user_name . "'s turn.");
        } else {
            $this->addMessage("Please place your move.");
        }
        $this->addMessage("You can /hit or /stand");
    }

    public function dealer_done(Game $Game, Player $Dealer)
    {
        if ($Game->getNumberOfPlayers() > 1) {
            $this->addMessage('All players have stood or are bust. The dealer draws cards:');
            $this->addMessage($Dealer->Hand->getHandString()  . " (" . $Dealer->Hand->Value . ")");
        } else {
            $this->addMessage('The dealer draws cards ' . $Dealer->Hand->getHandString() . " (" . $Dealer->Hand->Value . ")");
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
            $this->addMessage($Player->user_name . " wins " . $multiplier * $Player->bet . " coin!");
        } elseif ($multiplier == 0) {
            $this->addMessage($Player->user_name . " regains their bet of " . $Player->bet . ".");
        } elseif ($multiplier < 0) {
            $this->addMessage($Player->user_name . " loses their bet of " . $Player->bet . ".");
        }
    }

}