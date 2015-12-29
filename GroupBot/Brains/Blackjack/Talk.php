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
use GroupBot\Brains\Coin\Coin;

class Talk
{
    private $user_name;
    private $Messages = '';
    private $keyboard = false;

    private $Coin;

    public function __construct($user_name)
    {
        $this->user_name = $user_name;
        $this->Coin = new Coin();
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

    public function getKeyboard()
    {
        if ($this->keyboard) {
            return $this->keyboard;
        }
        return false;
    }

    public function join_game($bet)
    {
        $out = emoji(0x1F4B0) . " " . $this->user_name . " has joined the game";
        if ($bet > 0) {
            $out .= " with a bet of " . $bet . " coin.";
        } else {
            $out .= ".";
        }
        $this->addMessage($out);
        $this->addMessage(emoji(0x1F449) . " Others can also join the game with /blackjack");
        $this->addMessage(emoji(0x1F449) . " You can start the game with /bjstart");
        $this->keyboard = [["/bjstart"]];
    }

    public function start_game(Game $Game)
    {
        if ($Game->getNumberOfPlayers() > 1) {
            $this->addMessage("The game begins with " . $Game->getNumberOfPlayers() . " players.");
        }

        $this->addMessage("The dealer draws " . $Game->Dealer->Hand->getHandString() . " (" . $Game->Dealer->Hand->Value . ")");
        foreach ($Game->Players as $Player) {
            $this->addMessage($Player->user_name . " has " . $Player->Hand->getHandString()  . " (" . $Player->Hand->Value . ")");
        }
        foreach ($Game->Players as $Player) {
            if ($Player->Hand->isBlackjack()) {
                $this->addMessage($Player->user_name . " has blackjack! They stand.");
            }
        }

        if (!$Game->areAllPlayersDone()) {
            if ($Game->getNumberOfPlayers() > 1) {
                $this->addMessage($Game->getCurrentPlayer()->user_name . " goes first.");
            } else {
                $this->addMessage("Please place your move.");
            }
            $this->next_turn_options($Game->getCurrentPlayer());
        }
    }

    public function stand()
    {
        $this->addMessage(emoji(0x1F44C) . " " . $this->user_name . " stands.");
    }

    public function blackjack()
    {
        $this->addMessage($this->user_name . " has blackjack!");
    }

    private function next_turn_options(Player $Player)
    {
        $split = "";
        if ($Player->State == PlayerState::Join && $Player->Hand->canSplit()) {
            $split = "/split, ";
            $this->keyboard = [["/hit", "/stand"], ["/doubledown", "/split", "/surrender"]];
        } else {
            $this->keyboard = [["/hit", "/stand"], ["/doubledown", "/surrender"]];
        }
        $this->addMessage(emoji(0x1F449) . " You can /hit, /stand, " . $split . "/doubledown or /surrender");

    }

    private function player_state(Player $Player)
    {
        if ($Player->State == PlayerState::Stand) {
            $this->addMessage($this->user_name . " stands.");
        } elseif ($Player->State == PlayerState::Bust) {
            $this->addMessage($this->user_name . " is bust.");
        } elseif ($Player->State == PlayerState::TwentyOne) {
            $this->addMessage($this->user_name . " has twenty one! " . $this->user_name . " stands.");
        }
    }

    public function hit(Player $Player)
    {
        $this->addMessage(emoji(0x1F44C) . " " . $this->user_name . " hits.");
        $this->addMessage($this->user_name . "'s cards: " . $Player->Hand->getHandString() . " (" . $Player->Hand->Value . ")");
        $this->player_state($Player);
    }

    public function split(Player $Player1, Player $Player2)
    {
        $this->addMessage(emoji(0x1F44C) . " " . $Player1->user_name . " has split their hand into two and matched their bet of " . ($Player1->bet + 0) . ". The dealer has dealt them one new card per hand.");
        $this->addMessage("Hand 1: " . $Player1->Hand->getHandString() . " (" . $Player1->Hand->Value . ")");
        $this->addMessage("Hand 2: " . $Player2->Hand->getHandString() . " (" . $Player2->Hand->Value . ")");
    }

    public function split_wrong_turn()
    {
        $this->addMessage("You can only split on your first turn.");
    }

    public function split_wrong_cards()
    {
        $this->addMessage("You can only split with two equal ranked cards on your first turn.");
    }

    public function split_dealer_not_enough_money()
    {
        $this->addMessage(COIN_TAXATION_BODY . " doesn't have enough Coin to accept a split, sorry.");
    }

    public function split_not_enough_money(Player $Player)
    {
        $this->addMessage($Player->user_name . ", you don't have enough money to split");
    }

    public function split_only_once()
    {
        $this->addMessage("You can only split once!");
    }

    public function surrender(Player $Player)
    {
        $this->addMessage($Player->user_name . " surrenders! The dealer returns half their bet.");
    }

    public function surrender_wrong_turn()
    {
        $this->addMessage("You can only surrender on your first turn!");
    }

    public function surrender_free(Player $Player)
    {
        $this->addMessage($Player->user_name . " surrenders! However, as they're on a free bet, they receive no Coin.");
    }

    public function double_down(Player $Player)
    {
        $this->addMessage(emoji(0x1F44C) . " " . $this->user_name . " doubles down, doubling their bet to " . ($Player->bet + 0) . ".");
        $this->addMessage($this->user_name . " is dealt another card.");
        $this->addMessage($this->user_name . "'s cards: " . $Player->Hand->getHandString() . " (" . $Player->Hand->Value . ")");
        $this->player_state($Player);
    }

    public function double_down_not_enough_money(Player $Player)
    {
        $this->addMessage($Player->user_name . ", you don't have enough money to double down.");
    }

    public function double_down_dealer_not_enough_money()
    {
        $this->addMessage(COIN_TAXATION_BODY . " doesn't have enough Coin to accept a double down, sorry.");
    }

    public function next_turn(Game $Game)
    {
        $Player = $Game->getCurrentPlayer();

        if ($Player->State == PlayerState::Join && $Player->player_no != 0) {
            $this->addMessage($Player->user_name . "'s hand: " . $Player->Hand->getHandString()  . " (" . $Player->Hand->Value . ")");
        }
        if (!($Player->State == PlayerState::Join && $Player->player_no == 0)) {
            $this->addMessage("Dealer's hand: " . $Game->Dealer->Hand->getHandString()  . " (" . $Game->Dealer->Hand->Value . ")");
        }
        if ($Game->getNumberOfPlayers() > 1) {
            $out = "It is now " . $Player->user_name . "'s turn";
            if ($Player->split == 1) {
                $out .= " (hand one)";
            } elseif ($Player->split == 2) {
                $out .= " (hand two)";
            }
            $this->addMessage($out . ".");
        } else {
            $this->addMessage("Please place your move.");
        }
        $this->next_turn_options($Player);
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
        $out = emoji(0x1F4B0);
        if ($multiplier > 0) {
            $out .= $Player->user_name . " wins " . ($multiplier * $Player->bet + 0) . " coin!";
        } elseif ($multiplier == 0) {
            if ($Player->free_bet) {
                $out .= $Player->user_name . " cannot regain a free bet";
            } else {
                $out .= $Player->user_name . " regains their bet of " . ($Player->bet + 0);
            }
        } elseif ($multiplier < 0) {
            $free = $Player->free_bet ? " free " : " ";
            $out .= $Player->user_name . " loses their" . $free . "bet of " . ($Player->bet + 0);
        }

        $out .= " (`" . round($this->Coin->SQL->GetUserById($Player->user_id)->balance,2) . "`)";
        $this->addMessage($out);
    }

    public function bet_invalid()
    {
        $this->addMessage("Please enter a valid bet.");
    }

    public function bet_mandatory()
    {
        $this->addMessage("You are betting with the mandatory bet of 1 Coin.");
    }

    public function bet_mandatory_failed()
    {
        $this->addMessage(COIN_TAXATION_BODY . " can't accept the mandatory bet of 1 Coin right now. You are betting 0 Coin.");
    }

    public function bet_too_high($balance)
    {
        $out = "You don't have that much Coin to bet.";
        if ($balance < 1) {
            $out .= " However, " . COIN_TAXATION_BODY . " can give you a free bet of 1 Coin if you wish.";
        }
        $this->addMessage($out);
    }

    public function bet_too_high_for_dealer()
    {
        $this->addMessage(COIN_TAXATION_BODY . " can't accept a bet that high right now.");
    }

    public function bet_free()
    {
        $this->addMessage(COIN_TAXATION_BODY . " has given you a free bet of 1 Coin. Welcome back!");
    }

    public function bet_free_failed()
    {
        $this->addMessage(COIN_TAXATION_BODY . " isn't able to give you a free bet at the moment, sorry.");
    }

}