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

class Talk extends \GroupBot\Brains\CardGame\Talk
{
    public function turn_expired(\GroupBot\Brains\CardGame\Types\Player $player)
    {
        $this->addMessage(emoji(0x231B) . " " . $player->user->user_name . " hasn't made a move in over 5 minutes. They automatically stand.");
    }

    /**
     * @param Game $game
     */
    public function pre_game_status(\GroupBot\Brains\CardGame\Types\Game $game)
    {
        $out = emoji(0x1F4E2) . " Waiting for players to join the game.\nCurrent players: ";
        foreach ($game->Players as $player) {
            $out .= "*" . $player->user->user_name . "*, ";
        }
        $out = substr($out, 0, -2);
        $out .= "\nOther players can join with /blackjack, or you can start the game with /bjstart";
        $this->addMessage($out);
    }

    /**
     * @param Game $game
     */
    public function game_status(\GroupBot\Brains\CardGame\Types\Game $game)
    {
        $out = emoji(0x1F4E2) . " A game is in progress. The table is set as follows:";
        $out .= "\nDealer: " . $game->Dealer->Hand->getHandString();
        foreach ($game->Players as $player) {
            switch ($player->State) {
                case PlayerState::BlackJack:
                    $state = "Blackjack";
                    break;
                case PlayerState::Bust:
                    $state = "Bust";
                    break;
                case PlayerState::Hit:
                    $state = "Hit";
                    break;
                case PlayerState::Stand:
                    $state = "Stand";
                    break;
                case PlayerState::Surrender:
                    $state = "Surrender";
                    break;
                case PlayerState::TwentyOne:
                    $state = "Twenty one";
                    break;
                case PlayerState::Join:
                    $state = "Waiting";
                    break;
                default:
                    $state = "";
                    break;
            }
            $out .= "\n" . $player->user->user_name . ": " . $player->Hand->getHandString() . " _(" . $state . ", " . emoji(0x1F4B0) . "_`" . $player->bet . "`_)_";
        }
        $out .= "\n" .  emoji(0x1F449) . "It is now *" . $game->getCurrentPlayer()->user->user_name . "'s* turn.";
        $this->addMessage($out);
    }

    public function join_game(\GroupBot\Brains\CardGame\Types\Player $player)
    {
        $out = emoji(0x1F4B0) . " " . $player->user->user_name . " has joined the game";
        if ($player->bet > 0) {
            $out .= " with a bet of " . $player->bet . " coin.";
        } else {
            $out .= ".";
        }
        $this->addMessage($out);
        $this->addMessage(emoji(0x1F449) . " Others can also join the game with /blackjack");
        $this->addMessage(emoji(0x1F449) . " You can start the game with /bjstart");
    }

    /**
     * @param Game $Game
     */
    public function start_game(\GroupBot\Brains\CardGame\Types\Game $Game)
    {
        if ($Game->getNumberOfPlayers() > 1) {
            $this->addMessage("The game begins with " . $Game->getNumberOfPlayers() . " players.");
        }

        $this->addMessage("The dealer draws " . $Game->Dealer->Hand->getHandString() . " (" . $Game->Dealer->Hand->Value . ")");
        foreach ($Game->Players as $Player) {
            $this->addMessage($Player->user->user_name . " has " . $Player->Hand->getHandString()  . " (" . $Player->Hand->Value . ")");
        }
        foreach ($Game->Players as $Player) {
            if ($Player->Hand->isBlackjack()) {
                $this->addMessage($Player->user->user_name . " has blackjack! They stand.");
                $Player->no_blackjacks++;
            }
        }

        if (!$Game->areAllPlayersDone()) {
            if ($Game->getNumberOfPlayers() > 1) {
                $this->addMessage($Game->getCurrentPlayer()->user->user_name . " goes first.");
            } else {
                $this->addMessage("Please place your move.");
            }
            $this->next_turn_options($Game);
        }
    }

    public function stand(Player $player)
    {
        $this->addMessage(emoji(0x1F44C) . " " . $player->user->user_name . " stands.");
    }

    public function blackjack(Player $player)
    {
        $this->addMessage($player->user->user_name . " has blackjack!");
    }

    /**
     * @param Player $Player
     */
    private function next_turn_options(Game $game)
    {
        /** @var Player $Player */
        $Player = $game->getCurrentPlayer();
        $split = "";

        if ($game->getNumberOfPlayers() == 1) {
            if ($Player->State == PlayerState::Join && $Player->Hand->canSplit()) {
                $split = "/split, ";
                $this->keyboard = [["/hit", "/stand"], ["/doubledown", "/split", "/surrender"]];
            } else {
                $this->keyboard = [["/hit", "/stand"], ["/doubledown", "/surrender"]];
            }
        }
        $this->addMessage(emoji(0x1F449) . " You can /hit, /stand, " . $split . "/doubledown or /surrender");

    }

    private function player_state(Player $Player)
    {
        if ($Player->State == PlayerState::Stand) {
            $this->addMessage($Player->user->user_name . " stands.");
        } elseif ($Player->State == PlayerState::Bust) {
            $this->addMessage($Player->user->user_name . " is bust.");
        } elseif ($Player->State == PlayerState::TwentyOne) {
            $this->addMessage($Player->user->user_name . " has twenty one! " . $Player->user->user_name . " stands.");
        }
    }

    public function hit(Player $Player)
    {
        $this->addMessage(emoji(0x1F44C) . " " . $Player->user->user_name . " hits.");
        $this->addMessage($Player->user->user_name . "'s cards: " . $Player->Hand->getHandString() . " (" . $Player->Hand->Value . ")");
        $this->player_state($Player);
    }

    public function split(\GroupBot\Brains\CardGame\Types\Player $Player1, \GroupBot\Brains\CardGame\Types\Player $Player2)
    {
        $this->addMessage(emoji(0x1F44C) . " " . $Player1->user->user_name . " has split their hand into two and matched their bet of " . ($Player1->bet + 0) . ". The dealer has dealt them one new card per hand.");
        $this->addMessage("Hand 1: " . $Player1->Hand->getHandString() . " (" . $Player1->Hand->Value . ")");
        $this->addMessage("Hand 2: " . $Player2->Hand->getHandString() . " (" . $Player2->Hand->Value . ")");
    }

    public function split_wrong_turn()
    {
        $this->addMessage(emoji(0x1F44E) . "You can only split on your first turn.");
    }

    public function split_wrong_cards()
    {
        $this->addMessage(emoji(0x1F44E) . "You can only split with two equal ranked cards on your first turn.");
    }

    public function split_dealer_not_enough_money()
    {
        $this->addMessage(emoji(0x1F44E) . COIN_TAXATION_BODY . " doesn't have enough Coin to accept a split, sorry.");
    }

    public function split_not_enough_money(Player $Player)
    {
        $this->addMessage(emoji(0x1F44E) . $Player->user->user_name . ", you don't have enough money to split");
    }

    public function split_only_once()
    {
        $this->addMessage(emoji(0x1F44E) . "You can only split once!");
    }

    public function surrender(Player $player)
    {
        $out = $player->user->user_name . " surrenders! The dealer returns half their bet. ";
        $out .= " (`" . $player->user->getBalance() . "`)";
        $this->addMessage($out);
    }

    public function surrender_wrong_turn()
    {
        $this->addMessage(emoji(0x1F44E) . "You can only surrender on your first turn!");
    }

    public function surrender_free(Player $Player)
    {
        $this->addMessage($Player->user->user_name . " surrenders! However, as they're on a free bet, they receive no Coin.");
    }

    public function double_down(Player $Player)
    {
        $this->addMessage(emoji(0x1F44C) . " " . $Player->user->user_name . " doubles down, doubling their bet to " . ($Player->bet + 0) . ".");
        $this->addMessage($Player->user->user_name . " is dealt another card.");
        $this->addMessage($Player->user->user_name . "'s cards: " . $Player->Hand->getHandString() . " (" . $Player->Hand->Value . ")");
        $this->player_state($Player);
    }

    public function double_down_not_enough_money(Player $Player)
    {
        $this->addMessage(emoji(0x1F44E) . $Player->user->user_name . ", you don't have enough money to double down.");
    }

    public function double_down_dealer_not_enough_money()
    {
        $this->addMessage(emoji(0x1F44E) . COIN_TAXATION_BODY . " doesn't have enough Coin to accept a double down, sorry.");
    }

    /**
     * @param Game $Game
     */
    public function next_turn(\GroupBot\Brains\CardGame\Types\Game $Game)
    {
        /** @var Player $Player */
        $Player = $Game->getCurrentPlayer();

        if ($Player->State == PlayerState::Join && $Player->player_no != 0) {
            $this->addMessage($Player->user->user_name . "'s hand: " . $Player->Hand->getHandString()  . " (" . $Player->Hand->Value . ")");
        }
        if (!($Player->State == PlayerState::Join && $Player->player_no == 0)) {
            $this->addMessage("Dealer's hand: " . $Game->Dealer->Hand->getHandString()  . " (" . $Game->Dealer->Hand->Value . ")");
        }
        if ($Game->getNumberOfPlayers() > 1) {
            $out = "It is now " . $Player->user->user_name . "'s turn";
            if ($Player->split == 1) {
                $out .= " (hand one)";
            } elseif ($Player->split == 2) {
                $out .= " (hand two)";
            }
            $this->addMessage($out . ".");
        } else {
            $this->addMessage("Please place your move.");
        }
        $this->next_turn_options($Game);
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
}
