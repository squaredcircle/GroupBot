<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 14/12/2015
 * Time: 10:26 PM
 */

namespace GroupBot\Brains\Casinowar;


use GroupBot\Brains\CardGame\Types\Game;
use GroupBot\Brains\Casinowar\Enums\PlayerState;
use GroupBot\Brains\Casinowar\Types\Player;

class Talk extends \GroupBot\Brains\CardGame\Talk
{
    public function turn_expired(\GroupBot\Brains\CardGame\Types\Player $player)
    {
        $this->addMessage(emoji(0x231B) . " " . $player->user_name . " hasn't made a move in over 5 minutes. They automatically surrender.");
    }

    public function pre_game_status(\GroupBot\Brains\CardGame\Types\Game $game)
    {
        $out = emoji(0x1F4E2) . " Waiting for players to join the game.\nCurrent players: ";
        foreach ($game->Players as $player) {
            $out .= "*" . $player->user_name . "*, ";
        }
        $out = substr($out, 0, -2);
        $out .= "\nOther players can join with /casinowar, or you can start the game with /cwstart";
        $this->addMessage($out);
    }

    public function game_status(\GroupBot\Brains\CardGame\Types\Game $game)
    {
        $out = emoji(0x1F4E2) . " A game is in progress. The table is set as follows:";
        $out .= "\nDealer: " . $game->Dealer->Hand->getHandString();
        foreach ($game->Players as $player) {
            switch ($player->State) {
                case PlayerState::Win:
                    $state = "Win";
                    break;
                case PlayerState::Lose:
                    $state = "Lose";
                    break;
                case PlayerState::Draw:
                    $state = "Draw";
                    break;
                case PlayerState::Surrender | PlayerState::SurrenderForced:
                    $state = "Surrender";
                    break;
                case PlayerState::WarVictory | PlayerState::War:
                    $state = "War";
                    break;
                case PlayerState::Join:
                    $state = "Waiting";
                    break;
                default:
                    $state = "";
                    break;
            }
            $out .= "\n" . $player->user_name . ": " . $player->Hand->getHandString() . " _(" . $state . ", " . emoji(0x1F4B0) . "_`" . $player->bet . "`_)_";
        }
        $out .= "\n" .  emoji(0x1F449) . " It is now *" . $game->getCurrentPlayer()->user_name . "'s* turn.";
        $this->addMessage($out);
    }

    public function join_game(\GroupBot\Brains\CardGame\Types\Player $player)
    {
        $out = emoji(0x1F4B0) . " " . $player->user_name . " has joined the game";
        if ($player->bet > 0) {
            $out .= " with a bet of " . $player->bet . " coin.";
        } else {
            $out .= ".";
        }
        $this->addMessage($out);
        $this->addMessage(emoji(0x1F449) . " Others can also join the game with /casinowar");
        $this->addMessage(emoji(0x1F449) . " You can start the game with /cwstart");
    }

    public function start_game(\GroupBot\Brains\CardGame\Types\Game $game)
    {
        if ($game->getNumberOfPlayers() > 1) {
            $this->addMessage("The game begins with " . $game->getNumberOfPlayers() . " players.");
        }

        $this->addMessage("The dealer draws " . $game->Dealer->Hand->getHandString());
        foreach ($game->Players as $Player) {
            $this->addMessage($Player->user_name . " has " . $Player->Hand->getHandString());
        }

        if (!$game->areAllPlayersDone()) {
            $player = $game->getCurrentPlayer();
            if ($game->getNumberOfPlayers() > 1) {
                $this->addMessage($player->user_name . " drew. Please place your move.");
            } else {
                $this->addMessage("You drew. Please place your move.");
            }

            if ($game->getNumberOfPlayers() == 1) $this->keyboard = [["/surrender", "/war"]];
            $this->addMessage(emoji(0x1F449) . " You can /surrender or go to /war");
        }
    }

    public function next_turn(\GroupBot\Brains\CardGame\Types\Game $game)
    {
        /** @var Player $Player */
        $player = $game->getCurrentPlayer();
        $this->addMessage("It is now " . $player->user_name . "'s turn. Please place your move.");
        if ($game->getNumberOfPlayers() == 1) $this->keyboard = [["/surrender", "/war"]];
        $this->addMessage(emoji(0x1F449) . " You can /surrender or go to /war");
    }

    public function surrender_free(Player $player)
    {
        $this->addMessage(emoji(0x1F4B0) . " " . $player->user_name . " surrenders! However, as they're on a free bet, they receive no Coin.");
    }

    public function surrender(Player $player)
    {
        $out = emoji(0x1F4B0) . " " . $player->user_name . " surrenders! The dealer returns half their bet ";
        $out .= " (`" . $this->Coin->SQL->GetUserById($player->user_id)->getBalance() . "`)";
        $this->addMessage($out);
    }

    public function hand(Player $player)
    {
        $this->addMessage($player->user_name . " has " . $player->Hand->getHandString());
    }

    public function war_begins(Game $game)
    {
        if ($game->getNumberOfPlayers() > 1) $this->addMessage("All remaining players have made their moves. War begins.");
    }

    public function war(Player $player)
    {
        $this->addMessage(emoji(0x1F4A5) . " " . $player->user_name . " matches their bet, and goes to war!");
    }

    public function war_free_bet()
    {
        $this->addMessage(emoji(0x1F44E) . " Sorry, you don't have enough Coin to go to war. \nYou automatically surrender instead.");
    }

    public function war_not_enough_money()
    {
        $this->addMessage(emoji(0x1F44E) . " Sorry, you don't have enough Coin to go to war. \nYou automatically surrender instead.");
    }

    public function war_dealer_not_enough_money()
    {
        $this->addMessage(emoji(0x1F44E) . " " . COIN_TAXATION_BODY . " doesn't have enough Coin to accept a war bet, sorry. \nYou automatically surrender instead.");
    }
}