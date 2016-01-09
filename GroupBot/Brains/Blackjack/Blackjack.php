<?php

namespace GroupBot\Brains\Blackjack;

use GroupBot\Base\Telegram;
use GroupBot\Brains\Blackjack\Database\Control;
use GroupBot\Brains\Blackjack\Enums\PlayerMove;
use GroupBot\Brains\Blackjack\Enums\PlayerState;
use GroupBot\Brains\Blackjack\Types\Player;
use GroupBot\Brains\Coin\Coin;
use GroupBot\Brains\Coin\Enums\TransactionType;
use GroupBot\Brains\Coin\Types\Transaction;
use GroupBot\Types\User;

class Blackjack
{
    protected $chat_id;
    protected $user_id;
    protected $user_name;
    protected $bet;
    protected $free_bet;

    private $Game;
    private $DbControl;
    private $Coin;
    public $Talk;

    public function __construct(User $User, $chat_id, PlayerMove $Move, $bet)
    {
        $this->chat_id = $chat_id;
        $this->user_id = $User->id;
        $this->user_name = $User->first_name;
        $this->bet = $bet;
        $this->free_bet = false;

        $this->DbControl = new Control($chat_id);
        $this->Talk = new Talk($this->user_name);
        $this->Coin = new Coin();

        if (!$this->Game = $this->loadOrCreateGame($Move)) return false;
        $this->processPlayerMove($Move);
    }

    private function loadOrCreateGame(PlayerMove $Move)
    {
        if (!$Game = $this->DbControl->getGame()) {
            if (!$this->checkPlayerBet()) return false;
            $this->DbControl->insert_game();
            $Game = $this->DbControl->getGame();
            $Game->addDealer();
            $Game->addPlayer($this->user_id, $this->user_name, $this->bet, $this->free_bet, 0);
            if ($Move == PlayerMove::JoinGame) $this->Talk->join_game($this->bet);
        }
        return $Game;
    }

    private function checkPlayerBet()
    {
        $balance = $this->Coin->SQL->GetUserById($this->user_id)->balance;
        $betting_pool = isset($this->Game) ? $this->Game->betting_pool : 0;
        $TaxationBody = $this->Coin->SQL->GetUserByName(COIN_TAXATION_BODY);

        if (!(is_numeric($this->bet) && $this->bet >= 0 && $this->bet == round($this->bet, 2))) {
            $this->Talk->bet_invalid();
            return false;
        } elseif ($balance < 1 && $this->bet <= 1) {
            if ($TaxationBody->balance > $betting_pool + 1.5) {
                $this->Talk->bet_free();
                $this->bet = 1;
                $this->free_bet = true;
                return true;
            } else {
                $this->Talk->bet_free_failed();
                return false;
            }
        } elseif ($this->bet == 0) {
            if ($TaxationBody->balance > $betting_pool + 1.5) {
                $this->bet = 1;
                $this->Talk->bet_mandatory();
            } else {
                $this->Talk->bet_mandatory_failed();
            }
            return true;
        } elseif ($this->bet >= $balance) {
            $this->Talk->bet_too_high($balance);
            return false;
        }

        if ($TaxationBody->balance < $betting_pool + 1.5 * $this->bet) {
            $this->Talk->bet_too_high_for_dealer();
            return false;
        }
        return true;
    }

    private function processPlayerMove(PlayerMove $Move)
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
                if (!$this->checkPlayerBet()) return false;
                $this->Game->addPlayer($this->user_id, $this->user_name, $this->bet, $this->free_bet, 0);
                $this->Talk->join_game($this->bet);
            }
        }
        elseif ($this->Game->isPlayerInGame($this->user_id))
        {
            if ($Move == PlayerMove::StartGame) {
                $this->Game->startGame();
                $this->Game->saveGame();
                $this->Talk->start_game($this->Game);
                if ($this->Game->areAllPlayersDone()) {
                    $this->finaliseGame();
                    $this->Game->endGame();
                }
            } elseif ($Move == PlayerMove::QuitGame) {
                $this->Game->endGame();
            }
        }
    }

    private function processTurn(PlayerMove $Move)
    {
        $Player = $this->Game->getCurrentPlayer();
        $TaxationBody = $this->Coin->SQL->GetUserByName(COIN_TAXATION_BODY);

        if ($Player->State == PlayerState::BlackJack) {
            $this->Talk->blackjack();
        } elseif ($Player->State == PlayerState::Join || $Player->State == PlayerState::Hit) {
            switch ($Move) {
                case PlayerMove::Stand:
                    $Player->State =  new PlayerState(PlayerState::Stand);
                    $this->Talk->stand();
                    break;
                case PlayerMove::Hit:
                    $Player->Hand->addCard($this->Game->Deck->dealCard());
                    $this->setPlayerState(PlayerState::Hit);
                    $this->Talk->hit($Player);
                    break;
                case PlayerMove::DoubleDown:
                    if ($TaxationBody->balance > $Player->bet) {
                        if ($TaxationBody->balance > $this->Game->betting_pool + $Player->bet) {
                            $this->Coin->Transact->performTransaction(new Transaction(
                                NULL, $this->Coin->SQL->GetUserById($Player->user_id), $TaxationBody, $Player->bet, new TransactionType(TransactionType::BlackjackBet)
                            ));
                            $Player->bet = $Player->bet * 2;
                            $Player->Hand->addCard($this->Game->Deck->dealCard());
                            $this->setPlayerState(PlayerState::Stand);
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
                            if ($TaxationBody->balance > $Player->bet) {
                                if ($TaxationBody->balance > $this->Game->betting_pool + $Player->bet) {
                                    $this->Game->addPlayer($this->user_id, $this->user_name, $Player->bet, false, 2);
                                    $Player->Hand->addCard($this->Game->Deck->dealCard());
                                    $this->setPlayerState(PlayerState::Hit);
                                    $Player->split = 1;
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
                        $this->Talk->surrender_free($Player);
                    } elseif ($Player->State == PlayerState::Join) {
                        $this->taxationBodyTransact($Player, $Player->bet * 0.5);
                        $Player->State = new PlayerState(PlayerState::Surrender);
                        $this->Talk->surrender($Player);
                    } else {
                        $this->Talk->surrender_wrong_turn();
                        return false;
                    }
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

    private function setPlayerState($DefaultPlayerState)
    {
        $Player = $this->Game->getCurrentPlayer();

        if ($Player->Hand->isBust()) $Player->State =  new PlayerState(PlayerState::Bust);
        elseif ($Player->Hand->isTwentyOne()) $Player->State =  new PlayerState(PlayerState::TwentyOne);
        else $Player->State = new PlayerState($DefaultPlayerState);
    }

    private function cyclePlayer()
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
        $Telegram = new Telegram();
        $TaxationBody = $this->Coin->SQL->GetUserByName(COIN_TAXATION_BODY);

        if ($multiplier > 0) {
            if ($TaxationBody->balance > (1 + $multiplier) * $Player->bet) {
                $this->taxationBodyTransact($Player, (1 + $multiplier) * $Player->bet);
            } elseif ($TaxationBody->balance > abs($Player->bet)) {
                $Telegram->talk($this->chat_id, COIN_TAXATION_BODY . " doesn't have enough money to pay you, fam, but it can at least return your bet.");
                $this->taxationBodyTransact($Player, abs($Player->bet));
            } else {
                $Telegram->talk($this->chat_id, COIN_TAXATION_BODY . " doesn't have enough money to pay you, fam...\nsorry.");
            }
        } elseif ($multiplier == 0 && !$Player->free_bet) {
            if ($TaxationBody->balance > abs($Player->bet)) {
                $this->taxationBodyTransact($Player, abs($Player->bet));
            } else {
                $Telegram->talk($this->chat_id, COIN_TAXATION_BODY . " doesn't have enough money to repay you, fam...\nsorry.");
            }
        }

        $this->Talk->player_result($Player, $multiplier);
    }

    private function taxationBodyTransact(Player $Player, $amount)
    {
        $TaxationBody = $this->Coin->SQL->GetUserByName(COIN_TAXATION_BODY);

        $this->Coin->Transact->performTransaction(new Transaction(
            NULL,
            $TaxationBody,
            $this->Coin->SQL->GetUserById($Player->user_id),
            $amount,
            new TransactionType(TransactionType::BlackjackWin)
        ));
    }
}
