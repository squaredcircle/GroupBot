<?php

namespace GroupBot\Brains\Blackjack;

use GroupBot\Base\Telegram;
use GroupBot\Brains\Blackjack\Database\Control;
use GroupBot\Brains\Blackjack\Enums\PlayerMove;
use GroupBot\Brains\Blackjack\Enums\PlayerState;
use GroupBot\Brains\Blackjack\Types\Player;
use GroupBot\Brains\Coin;
use GroupBot\Types\User;

class Blackjack
{
    protected $chat_id;
    protected $user_id;
    protected $user_name;

    private $Game;
    private $DbControl;
    public $Talk;

    public function __construct(User $User, $chat_id, PlayerMove $Move, $bet)
    {
        $this->chat_id = $chat_id;
        $this->user_id = $User->id;
        $this->user_name = $User->first_name;

        $this->DbControl = new Control($chat_id);
        $this->Talk = new Talk($this->user_name);

        $this->Game = $this->loadOrCreateGame($Move, $bet);
        $this->processPlayerMove($Move, $bet);
    }

    private function loadOrCreateGame(PlayerMove $Move, $bet)
    {
        if (!$Game = $this->DbControl->getGame()) {
            $this->DbControl->insert_game();
            $Game = $this->DbControl->getGame();
            $Game->addDealer();
            $Game->addPlayer($this->user_id, $this->user_name, $bet);
            if ($Game->Players[0]->State == PlayerState::BlackJack) {
                $this->Talk->blackjack();
            }
            if ($Move == PlayerMove::JoinGame) $this->Talk->join_game($bet);
        }
        return $Game;
    }

    private function processPlayerMove(PlayerMove $Move, $bet)
    {
        if ($this->Game->isGameStarted())
        {
            if ($this->Game->getCurrentPlayer()->user_id == $this->user_id
                && $Move != PlayerMove::JoinGame && $Move != PlayerMove::StartGame) {
                $this->processTurn($Move);
            }
        }
        elseif (!$this->Game->isPlayerInGame($this->user_id))
        {
            if ($Move == PlayerMove::JoinGame) {
                $this->Game->addPlayer($this->user_id, $this->user_name, $bet);
                $this->Talk->join_game($bet);
            }
        }
        elseif ($this->Game->isPlayerInGame($this->user_id))
        {
            if ($Move == PlayerMove::StartGame) {
                $this->Game->startGame();
                $this->Game->saveGame();
                $this->Talk->start_game($this->Game);
            } elseif ($Move == PlayerMove::QuitGame) {
                $this->Game->endGame();
            }
        }
    }

    private function processTurn(PlayerMove $Move)
    {
        $Player = $this->Game->getCurrentPlayer();

        if ($Player->State == PlayerState::Join || $Player->State == PlayerState::Hit) {
            switch ($Move) {
                case PlayerMove::Stand:
                    $Player->State =  new PlayerState(PlayerState::Stand);
                    $this->Talk->stand();
                    break;
                case PlayerMove::Hit:
                    $Player->Hand->addCard($this->Game->Deck->dealCard());

                    if ($Player->Hand->isBust()) $Player->State =  new PlayerState(PlayerState::Bust);
                    elseif ($Player->Hand->isTwentyOne()) $Player->State =  new PlayerState(PlayerState::TwentyOne);
                    else $Player->State = new PlayerState(PlayerState::Hit);

                    $this->Talk->hit($Player);

                    break;
            }
        }
        $this->Game->savePlayer();
        if ($this->cyclePlayer()) {
            $this->Game->saveGame();
            $this->Talk->next_turn($this->Game);
        } else {
            $this->finaliseGame();
            $this->Game->endGame();
        }
    }

    private function cyclePlayer()
    {
        $Player = $this->Game->getCurrentPlayer();
        if ($Player->State == PlayerState::Join || $Player->State == PlayerState::Hit) {
            return true;
        }

        if (++$this->Game->turn == $this->Game->getNumberOfPlayers()) {
            return false;
        }

        return true;
    }

    private function finaliseGame()
    {
        $Dealer = $this->Game->Dealer;
        do {
            $Dealer->Hand->addCard($this->Game->Deck->dealCard());
        } while (!$Dealer->Hand->isDealerDone());

        if ($Dealer->Hand->isBust()) $Dealer->State =  new PlayerState(PlayerState::Bust);
        elseif ($Dealer->Hand->isTwentyOne()) $Dealer->State =  new PlayerState(PlayerState::TwentyOne);
        else $Dealer->State = new PlayerState(PlayerState::Stand);

        $this->Talk->dealer_done($this->Game, $Dealer);

        foreach ($this->Game->Players as $Player)
        {
            if ($Player->State == PlayerState::Stand)
            {
                if ($Dealer->Hand->isBust()) {
                    $this->payPlayer($Player, 1.0);
                } elseif ($Dealer->Hand->isBlackjack() || $Dealer->Hand->isTwentyOne()) {
                    $this->payPlayer($Player, -1.0);
                } else {
                    if ($Player->Hand->Value > $Dealer->Hand->Value) {
                        $this->payPlayer($Player, 1.0);
                    } elseif ($Player->Hand->Value == $Dealer->Hand->Value) {
                        $this->payPlayer($Player, 0.0);
                    }  elseif ($Player->Hand->Value < $Dealer->Hand->Value) {
                        $this->payPlayer($Player, -1.0);
                    }
                }
            }
            elseif ($Player->State == PlayerState::Bust)
            {
                $this->payPlayer($Player, -1.0);
            }
            elseif ($Player->State == PlayerState::TwentyOne)
            {
                if ($Dealer->Hand->isBlackjack()) {
                    $this->payPlayer($Player, -1.0);
                } elseif ($Dealer->Hand->isTwentyOne()) {
                    $this->payPlayer($Player, 0.0);
                } else {
                    $this->payPlayer($Player, 1.0);
                }
            }
            elseif ($Player->State == PlayerState::BlackJack)
            {
                if ($Dealer->Hand->isBlackjack()) {
                    $this->payPlayer($Player, 0.0);
                } else {
                    $this->payPlayer($Player, 1.5);
                }
            }
        }
    }

    private function payPlayer(Player $Player, $multiplier)
    {
        $this->Talk->player_result($Player, $multiplier);
        if ($multiplier != 0) {
            $Telegram = new Telegram();
            $Coin = new Coin();

            if ($multiplier > 0) {
                if ($Coin->getBalanceByUserName(TAXATION_BODY) > $multiplier * $Player->bet) {
                    $Coin->taxationBodyTransact($Player->user_id, $multiplier * $Player->bet, $Telegram);
                } else {
                    $Telegram->talk($this->chat_id, TAXATION_BODY . " doesn't have enough money to pay you, fam... \nsorry.");
                }
            } elseif ($multiplier < 0) {
                $Coin->performTransaction($Player->user_id, TAXATION_BODY, abs($multiplier * $Player->bet), $Telegram);
            }
        }
    }
}
