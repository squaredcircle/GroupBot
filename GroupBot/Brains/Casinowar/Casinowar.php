<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 22/01/2016
 * Time: 7:21 PM
 */

namespace GroupBot\Brains\Casinowar;


use GroupBot\Brains\CardGame\CardGame;
use GroupBot\Brains\CardGame\Enums\GameResult;
use GroupBot\Brains\Casinowar\Enums\PlayerMove;
use GroupBot\Brains\Casinowar\Enums\PlayerState;
use GroupBot\Brains\Casinowar\Types\Game;
use GroupBot\Brains\Casinowar\Types\Player;
use GroupBot\Brains\Coin\Enums\TransactionType;
use GroupBot\Brains\Coin\Types\Transaction;

class Casinowar extends CardGame
{
    /** @var SQL */
    protected $SQL;
    /** @var Talk */
    public $Talk;
    /** @var Game */
    public $Game;

    /**
     * @return SQL
     */
    protected function newSQL()
    {
        return new SQL();
    }

    /**
     * @param $user_name
     * @return Talk
     */
    protected function newTalk()
    {
        return new Talk();
    }

    /**
     * @param $playerMove
     * @return PlayerMove
     */
    protected function newPlayerMove($playerMove)
    {
        return new PlayerMove($playerMove);
    }

    private function surrender(Player $player)
    {
        $player->State =  new PlayerState(PlayerState::Surrender);
        $player->no_surrenders++;
        $player->game_result = new GameResult(GameResult::Loss);

        if ($player->free_bet) {
            $this->Talk->surrender_free($player);
        } else {
            $this->Bets->taxationBodyTransact($player, $player->bet * 0.5);
            $player->bet_result = $player->bet * (-0.5);
            $this->Talk->surrender($player);
        }
    }

    /**
     * @param PlayerMove $Move
     * @return bool
     */
    protected function processTurnActions(\GroupBot\Brains\CardGame\Enums\PlayerMove $Move)
    {
        /** @var Player $Player */
        $Player = $this->Game->getCurrentPlayer();

        if ($Player->State == PlayerState::Draw) {
            switch ($Move) {
                case PlayerMove::Surrender | PlayerMove::DefaultMove:
                    $this->surrender($Player);
                    break;
                case PlayerMove::War:
                    $Player->Hand->addCard($this->Game->Deck->dealCard());

                    $player_coin = $this->Coin->SQL->GetUserById($Player->user_id);
                    $TaxationBody = $this->Coin->SQL->GetUserByName(COIN_TAXATION_BODY);

                    if ($Player->free_bet) {
                        $this->Talk->war_free_bet();
                        $this->surrender($Player);
                        return true;
                    } elseif ($Player->bet > $player_coin->getBalance()) {
                        $this->Talk->war_not_enough_money();
                        $this->surrender($Player);
                        return true;
                    } elseif ($TaxationBody->getBalance() < $this->Game->betting_pool + $Player->bet) {
                        $this->Talk->war_dealer_not_enough_money();
                        $this->surrender($Player);
                        return true;
                    } else {
                        $this->Coin->Transact->performTransaction(new Transaction(
                            NULL, $player_coin, $TaxationBody, $Player->bet,new TransactionType(TransactionType::CasinoWarBet)
                        ));
                        $Player->bet = $Player->bet * 2;
                    }
                    $Player->State = new PlayerState(PlayerState::War);
                    $Player->no_wars++;
                    $this->Talk->war($Player);
                    break;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    protected function cyclePlayer()
    {
        /** @var Player $Player */
        $Player = $this->Game->getCurrentPlayer();
        if ($Player->State == PlayerState::Draw) {
            return true;
        }

        do {
            if (++$this->Game->turn == $this->Game->getNumberOfPlayers()) {
                return false;
            }
        } while ($this->Game->getCurrentPlayer()->State != PlayerState::Draw);

        return true;
    }

    /**
     * @return bool
     */
    protected function finaliseGame()
    {
        if ($this->Game->isWar()) {
            $this->Game->Dealer->Hand->addCard($this->Game->Deck->dealCard());
            $this->Talk->war_begins($this->Game);
            $this->Talk->hand($this->Game->Dealer);
            foreach ($this->Game->Players as $player) {
                if ($player->State == PlayerState::War) {
                    if ($player->Hand->Value >= $this->Game->Dealer->Hand->Value) $player->State = new PlayerState(PlayerState::WarVictory);
                    if ($player->Hand->Value < $this->Game->Dealer->Hand->Value) $player->State = new PlayerState(PlayerState::Lose);
                    $this->Talk->hand($player);
                }
            }
        }

        foreach ($this->Game->Players as $player) {
            if ($player->State == PlayerState::Win) {
                $this->Bets->payPlayer($player, 1.0);
            } elseif ($player->State == PlayerState::WarVictory) {
                    $this->Bets->payPlayer($player, 0.5);
            } elseif ($player->State == PlayerState::Lose) {
                $this->Bets->payPlayer($player, -1.0);
            }
        }
    }
}
