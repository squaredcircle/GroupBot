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
        foreach ($Game->Players as $Player) {
            if ($Player->Hand->isBlackjack()) {
                $this->addMessage($Player->user_name . " has blackjack! They stand.");
            }
        }

        if ($Game->getNumberOfPlayers() > 1) {
            $this->addMessage($Game->getCurrentPlayer()->user_name . " goes first.");
        } else {
            $this->addMessage("Please place your move.");
        }
        $this->next_turn_options($Game->getCurrentPlayer());
    }

    public function stand()
    {
        $this->addMessage($this->user_name . " stands.");
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
        }
        $this->addMessage("You can /hit, /stand, " . $split . "/doubledown or /surrender");
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
        $this->addMessage($this->user_name . " hits.");
        $this->addMessage($this->user_name . "'s cards: " . $Player->Hand->getHandString() . " (" . $Player->Hand->Value . ")");
        $this->player_state($Player);
    }

    public function split(Player $Player1, Player $Player2)
    {
        $this->addMessage($Player1->user_name . " has split their hand into two and matched their bet. The dealer has dealt them one new card per hand.");
        $this->addMessage("Hand 1: " . $Player1->Hand->getHandString() . " (" . $Player1->Hand->Value . ")");
        $this->addMessage("Hand 2: " . $Player2->Hand->getHandString() . " (" . $Player2->Hand->Value . ")");
        $this->addMessage($Player1->user_name . " is now playing their first hand");
        $this->next_turn_options($Player1);
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
        $this->addMessage(TAXATION_BODY . " doesn't have enough Coin to accept a split, sorry.");
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
        $this->addMessage($this->user_name . " doubles down, doubling their bet to " . $Player->bet . ".");
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
        $this->addMessage(TAXATION_BODY . " doesn't have enough Coin to accept a double down, sorry.");
    }

    public function next_turn(Game $Game)
    {
        $Player = $Game->getCurrentPlayer();
        if ($Game->getNumberOfPlayers() > 1) {
            $out = "It is now " . $Player->user_name . "'s turn";
            if ($Player->split == 1) {
               $out .= " _(first hand)_";
            } elseif ($Player->split == 2) {
                $out .= " _(second hand)_";
            }
            $this->addMessage($out . ".");
        } else {
            $this->addMessage("Please place your move.");
        }
        if ($Player->State == PlayerState::Join && $Player->player_no != 0) {
            $this->addMessage($Player->user_name . "'s hand: " . $Player->Hand->getHandString()  . " (" . $Player->Hand->Value . ")");
        }
        if (!($Player->State == PlayerState::Join && $Player->player_no == 0)) {
            $this->addMessage("Dealer's hand: " . $Game->Dealer->Hand->getHandString()  . " (" . $Game->Dealer->Hand->Value . ")");
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
        if ($multiplier > 0) {
            $this->addMessage($Player->user_name . " wins " . $multiplier * $Player->bet . " coin!");
        } elseif ($multiplier == 0) {
            if ($Player->free_bet) {
                $this->addMessage($Player->user_name . " cannot regain a free bet.");
            } else {
                $this->addMessage($Player->user_name . " regains their bet of " . $Player->bet . ".");
            }
        } elseif ($multiplier < 0) {
            $free = $Player->free_bet ? " free " : " ";
            $this->addMessage($Player->user_name . " loses their" . $free . "bet of " . $Player->bet . ".");
        }
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
        $this->addMessage(TAXATION_BODY . " can't accept the mandatory bet of 1 Coin right now. You are betting 0 Coin.");
    }

    public function bet_too_high($balance)
    {
        $out = "You don't have that much Coin to bet.";
        if ($balance < 1) {
            $out .= " However, " . TAXATION_BODY . " can give you a free bet of 1 Coin if you wish.";
        }
        $this->addMessage($out);
    }

    public function bet_too_high_for_dealer()
    {
        $this->addMessage(TAXATION_BODY . " can't accept a bet that high right now.");
    }

    public function bet_free()
    {
        $this->addMessage(TAXATION_BODY . " has given you a free bet of 1 Coin. Welcome back!");
    }

    public function bet_free_failed()
    {
        $this->addMessage(TAXATION_BODY . " isn't able to give you a free bet at the moment, sorry.");
    }

}