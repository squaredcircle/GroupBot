<?php

namespace GroupBot\Brains\Blackjack;

use GroupBot\Brains\Blackjack\Types\Game;
use GroupBot\Brains\Blackjack\Types\Player;
use GroupBot\Brains\CardGame\Enums\GameResult;
use GroupBot\Brains\Blackjack\Enums\PlayerMove;
use GroupBot\Brains\Blackjack\Enums\PlayerState;
use GroupBot\Brains\CardGame\CardGame;
use GroupBot\Brains\Coin\Enums\TransactionType;
use GroupBot\Brains\Coin\Types\BankTransaction;

class Blackjack extends CardGame
{
    /** @var SQL */
    protected $SQL;
    /** @var Talk */
    public $Talk;
    /** @var Game */
    public $Game;

    protected function processTurnActions(\GroupBot\Brains\CardGame\Enums\PlayerMove $Move)
    {
        /** @var Player $Player */
        $Player = $this->Game->getCurrentPlayer();
        $bank = $this->Transact->UserSQL->getUserFromId(COIN_BANK_ID);

        if ($Player->State == PlayerState::BlackJack) {
            $Player->no_blackjacks++;
            $this->Talk->blackjack($Player);
        } elseif ($Player->State == PlayerState::Join || $Player->State == PlayerState::Hit) {
            switch ($Move) {
                case PlayerMove::Stand | PlayerMove::DefaultMove:
                    $Player->State =  new PlayerState(PlayerState::Stand);
                    $Player->no_stands++;
                    $this->Talk->stand($Player);
                    break;
                case PlayerMove::Hit:
                    $Player->Hand->addCard($this->Game->Deck->dealCard());
                    $this->setPlayerState(PlayerState::Hit);
                    $Player->no_hits++;
                    $this->Talk->hit($Player);
                    break;
                case PlayerMove::DoubleDown:
                    if ($this->user->getBalance()> $Player->bet) {
                        if ($bank->getBalance() > $this->Game->betting_pool + $Player->bet) {
                            $this->Transact->transactToBank(new BankTransaction(
                                $this->user, $Player->bet, new TransactionType(TransactionType::BlackjackBet)
                            ));
                            $Player->bet = $Player->bet * 2;
                            $Player->Hand->addCard($this->Game->Deck->dealCard());
                            $this->setPlayerState(PlayerState::Stand);
                            $Player->no_doubledowns++;
                            $this->Talk->double_down($Player);
                        } else {
                            $this->Talk->double_down_dealer_not_enough_money();
                            return false;
                        }
                    } else {
                        $this->Talk->double_down_not_enough_money($Player);
                        return false;
                    }
                    break;
                case PlayerMove::Split:
                    if ($Player->State != PlayerState::Join) {
                        $this->Talk->split_wrong_turn();
                        return false;
                    } elseif ($Player->Hand->canSplit()) {
                        if ($Player->split == 0) {
                            if ($bank->getBalance() > $Player->bet) {
                                if ($bank->getBalance() > $this->Game->betting_pool + $Player->bet) {
                                    $this->Game->addPlayer($this->user, $this->Bets, $Player->bet, false, 2);
                                    $Player->Hand->addCard($this->Game->Deck->dealCard());
                                    $this->setPlayerState(PlayerState::Hit);
                                    $Player->split = 1;
                                    $Player->no_splits++;
                                    $this->Talk->split($Player, $this->Game->getPlayer($Player->player_no + 1));
                                } else {
                                    $this->Talk->split_dealer_not_enough_money();
                                    return false;
                                }
                            } else {
                                $this->Talk->split_not_enough_money($Player);
                                return false;
                            }
                        } else {
                            $this->Talk->split_only_once();
                            return false;
                        }
                    } else {
                        $this->Talk->split_wrong_cards();
                        return false;
                    }
                    break;
                case PlayerMove::Surrender:
                    if ($Player->free_bet) {
                        $Player->State = new PlayerState(PlayerState::Surrender);
                        $Player->no_surrenders++;
                        $Player->game_result = new GameResult(GameResult::Loss);
                        $this->Talk->surrender_free($Player);
                    } elseif ($Player->State == PlayerState::Join) {
                        $this->Bets->taxationBodyTransact($Player, $Player->bet * 0.5);
                        $Player->State = new PlayerState(PlayerState::Surrender);
                        $Player->no_surrenders++;
                        $Player->game_result = new GameResult(GameResult::Loss);
                        $Player->bet_result = $Player->bet * (-0.5);
                        $this->Talk->surrender($Player);
                    } else {
                        $this->Talk->surrender_wrong_turn();
                        return false;
                    }
                    break;
            }
        }

        return true;
    }

    private function setPlayerState($DefaultPlayerState)
    {
        /** @var Player $Player */
        $Player = $this->Game->getCurrentPlayer();

        if ($Player->Hand->isBust()) $Player->State =  new PlayerState(PlayerState::Bust);
        elseif ($Player->Hand->isTwentyOne()) $Player->State =  new PlayerState(PlayerState::TwentyOne);
        else $Player->State = new PlayerState($DefaultPlayerState);
    }

    protected function cyclePlayer()
    {
        $Player = $this->Game->getCurrentPlayer();
        if ($Player->State == PlayerState::Join || $Player->State == PlayerState::Hit) {
            return true;
        }

        do {
            if (++$this->Game->turn == $this->Game->getNumberOfPlayers()) {
                return false;
            }
        } while ($this->Game->getCurrentPlayer()->State == PlayerState::BlackJack);

        return true;
    }

    protected function finaliseGame()
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
                    $this->Bets->payPlayer($Player, $Dealer->user, 1.0);
                } elseif ($Dealer->Hand->isBlackjack() || $Dealer->Hand->isTwentyOne()) {
                    $this->Bets->payPlayer($Player, $Dealer->user, -1.0);
                } else {
                    if ($Player->Hand->Value > $Dealer->Hand->Value) {
                        $this->Bets->payPlayer($Player, $Dealer->user, 1.0);
                    } elseif ($Player->Hand->Value == $Dealer->Hand->Value) {
                        $this->Bets->payPlayer($Player, $Dealer->user, 0.0);
                    }  elseif ($Player->Hand->Value < $Dealer->Hand->Value) {
                        $this->Bets->payPlayer($Player, $Dealer->user, -1.0);
                    }
                }
            }
            elseif ($Player->State == PlayerState::Bust)
            {
                $this->Bets->payPlayer($Player, $Dealer->user, -1.0);
            }
            elseif ($Player->State == PlayerState::TwentyOne)
            {
                if ($Dealer->Hand->isBlackjack()) {
                    $this->Bets->payPlayer($Player, $Dealer->user, -1.0);
                } elseif ($Dealer->Hand->isTwentyOne()) {
                    $this->Bets->payPlayer($Player, $Dealer->user, 0.0);
                } else {
                    $this->Bets->payPlayer($Player, $Dealer->user, 1.0);
                }
            }
            elseif ($Player->State == PlayerState::BlackJack)
            {
                if ($Dealer->Hand->isBlackjack()) {
                    $this->Bets->payPlayer($Player, $Dealer->user, 0.0);
                } else {
                    $this->Bets->payPlayer($Player, $Dealer->user, 1.5);
                }
            }
        }
    }

    /**
     * @param \PDO $db
     * @return SQL
     */
    protected function newSQL(\PDO $db)
    {
        return new SQL($db);
    }

    /**
     * @param $user_name
     * @return \GroupBot\Brains\Blackjack\Talk
     */
    protected function newTalk($user_name)
    {
        return new Talk();
    }

    /**
     * @param $playerMove
     * @return \GroupBot\Brains\CardGame\Enums\PlayerMove
     */
    protected function newPlayerMove($playerMove)
    {
        return new PlayerMove($playerMove);
    }
}
