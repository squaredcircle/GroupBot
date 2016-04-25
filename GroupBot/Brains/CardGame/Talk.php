<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 14/12/2015
 * Time: 10:26 PM
 */

namespace GroupBot\Brains\CardGame;


use GroupBot\Brains\CardGame\Types\Game;
use GroupBot\Brains\CardGame\Types\Player;

abstract class Talk
{
    protected $Messages = '';
    protected $keyboard = false;

    protected function addMessage($message)
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

    abstract public function turn_expired(Player $player);
    abstract public function pre_game_status(Game $game);
    abstract public function game_status(Game $game);
    abstract public function join_game(Player $player);
    abstract public function start_game(Game $game);
    abstract public function next_turn(Game $game);

    public function player_result(Player $Player, $multiplier)
    {
        $out = emoji(0x1F4B0);
        if ($multiplier > 0) {
            $out .= "*" . $Player->user->getName() . "* wins " . ($multiplier * $Player->bet + 0) . " coin!";
        } elseif ($multiplier == 0) {
            if ($Player->free_bet) {
                $out .= "*" . $Player->user->getName() . "* cannot regain a free bet";
            } else {
                $out .= "*" . $Player->user->getName() . "* regains their bet of " . ($Player->bet + 0);
            }
        } elseif ($multiplier < 0) {
            $free = $Player->free_bet ? " free " : " ";
            $out .= "*" . $Player->user->getName() . "* loses their" . $free . "bet of " . ($Player->bet + 0);
        }

        $out .= " (`" . $Player->user->getBalance() . "`)";
        $this->addMessage($out);
    }

    public function bet_invalid()
    {
        $this->addMessage(emoji(0x1F44E) . " Please enter a valid bet.");
    }

    public function bet_invalid_notation()
    {
        $this->addMessage(emoji(0x1F44E) . " Your bet doesn't make sense. You can use the word `all` in a sensible equation to calculate your bet.");
    }

    public function bet_invalid_calculation()
    {
        $this->addMessage(emoji(0x1F44E) . " Sorry, that calculates to an amount you cannot bet.");
    }

    public function bet_mandatory()
    {
        $this->addMessage("You are betting with the mandatory bet of 1 Coin.");
    }

    public function bet_mandatory_failed()
    {
        $this->addMessage(COIN_TAXATION_BODY . " can't accept the mandatory bet of 1 Coin right now. You are betting 0 Coin.");
    }

    public function bet_limit()
    {
        $this->addMessage(emoji(0x1F449) . " The betting limit per game is " . CASINO_BETTING_MAX . ". Your bet has been adjusted.");
    }

    public function bet_too_high($balance)
    {
        $out = emoji(0x1F44E) . " You don't have that much Coin to bet.";
        if ($balance < 1) {
            $out .= "\nHowever, " . COIN_TAXATION_BODY . " can give you a free bet of 1 Coin if you wish.";
        }
        $this->addMessage($out);
    }

    public function bet_too_high_for_dealer()
    {
        $this->addMessage(emoji(0x1F44E) . " " . COIN_TAXATION_BODY . " can't accept a bet that high right now.");
    }

    public function bet_calculation($value)
    {
        $this->addMessage(emoji(0x1F4DD) . " Okay, you've placed a bet of " . $value . " Coin.");
    }

    public function bet_free($free_bets_today)
    {
        $this->addMessage("You've less than 1 Coin, so " . COIN_TAXATION_BODY . " has given you a free bet of 1 Coin (*" . (CASINO_DAILY_FREE_BETS  - $free_bets_today - 1) .  "* left today). Welcome back!");
    }

    public function bet_free_failed()
    {
        $this->addMessage(emoji(0x1F44E) . " " . COIN_TAXATION_BODY . " isn't able to give you a free bet at the moment, sorry.");
    }

    public function bet_free_too_many()
    {
        $now = new \DateTime();
        $future_date = new \DateTime('tomorrow');
        $interval = $future_date->diff($now);
        $time = $interval->format("*%h hours* and *%i minutes*");

        $this->addMessage(emoji(0x1F44E) . " Sorry - you only get " . CASINO_DAILY_FREE_BETS . " free bets per day. Come back tomorrow!\n($time to go)");

        $this->keyboard = [
            [
                [
                    'text' => emoji(0x1F3AE) . ' Games Menu',
                    'callback_data' => '/help games'
                ],
                [
                    'text' => emoji(0x1F6AA) . ' Main menu',
                    'callback_data' => '/help'
                ]
            ]
        ];
    }

    public function pay_bet_failed_return()
    {
        $this->addMessage(emoji(0x1F44E) . " " . COIN_TAXATION_BODY . " doesn't have enough money to pay you, but it can at least return your bet.");
    }

    public function pay_bet_failed()
    {
        $this->addMessage(emoji(0x1F44E) . " " . COIN_TAXATION_BODY . " doesn't have enough money to pay you, fam...\nsorry.");
    }

    public function pay_bet_failed_repay()
    {
        $this->addMessage(emoji(0x1F44E) . " " . COIN_TAXATION_BODY . " doesn't have enough money to repay you, fam...\nsorry.");
    }

}
