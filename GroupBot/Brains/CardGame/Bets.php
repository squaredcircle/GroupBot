<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 13/02/2016
 * Time: 12:58 AM
 */

namespace GroupBot\Brains\CardGame;


use GroupBot\Brains\CardGame\Enums\GameResult;
use GroupBot\Brains\CardGame\Enums\GameType;
use GroupBot\Brains\CardGame\Types\Game;
use GroupBot\Brains\CardGame\Types\Player;
use GroupBot\Brains\Coin\Enums\TransactionType;
use GroupBot\Brains\Coin\Money\Transact;
use GroupBot\Brains\Coin\Types\BankTransaction;
use GroupBot\libraries\eos\Parser;
use GroupBot\Types\User;

class Bets
{
    private $Transact, $db, $Talk;
    public $bet, $free_bet;

    public function __construct(Talk $Talk, \PDO $db)
    {
        $this->Transact = new Transact($db);
        $this->free_bet = false;
        $this->Talk = $Talk;
        $this->db = $db;
    }

    /**
     * @param Game $game
     * @param User $user
     * @param User $bank
     * @param $bet
     * @return bool
     */
    public function checkPlayerBet(Game $game, User $user, User $bank, $bet)
    {
        switch ($game->GameType) {
            case GameType::Blackjack:
                $max_bet_factor = 1.5;
                break;
            case GameType::Casinowar:
                $max_bet_factor = 1.0;
                break;
            default:
                $max_bet_factor = 2.0;
                break;
        }
        $this->bet = $bet;

        if (!(is_numeric($this->bet) && $this->bet >= 0 && $this->bet == round($this->bet, 2))) {
            if (stripos($this->bet, "all") !== false) {
                try {
                    $value = Parser::solve($this->bet, array('all' => $user->getBalance(true)));
                    $value = round($value,4);
                    if ($value >=0 && $value <= $user->getBalance(true)) {
                        $this->bet = $value;
                        $this->Talk->bet_calculation(round($value,2));
                    } else {
                        $this->Talk->bet_invalid_calculation();
                        return false;
                    }
                } catch (\Exception $e) {
                    $this->Talk->bet_invalid_notation();
                    return false;
                }
            } else {
                $this->Talk->bet_invalid();
                return false;
            }
        }

        if ($this->bet > CASINO_BETTING_MAX) {
            $this->bet = CASINO_BETTING_MAX;
            $this->Talk->bet_limit();
        }

        if ($user->getBalance(true) < 1 && $this->bet <= 1) {
            if ($bank->getBalance() > $game->betting_pool + $max_bet_factor) {
                if ($user->free_bets_today < CASINO_DAILY_FREE_BETS) {
                    $this->Talk->bet_free($user->free_bets_today);
                    $this->bet = 1;
                    $this->free_bet = true;
                    $user->free_bets_today++;
                    $user->save($this->db);
                } else {
                    $this->Talk->bet_free_too_many();
                    return false;
                }
            } else {
                $this->Talk->bet_free_failed();
                return false;
            }
        } elseif ($this->bet < 1) {
            if ($bank->getBalance() > $game->betting_pool + $max_bet_factor) {
                $this->bet = 1;
                $this->Talk->bet_mandatory();
            } else {
                $this->bet = 0;
                $this->Talk->bet_mandatory_failed();
            }
            return true;
        } elseif ($this->bet > $user->getBalance(true)) {
            $this->Talk->bet_too_high($user->getBalance(true));
            return false;
        }

        if ($bank->getBalance() < $game->betting_pool + $max_bet_factor * $this->bet) {
            $this->Talk->bet_too_high_for_dealer();
            return false;
        }

        $game->betting_pool += $this->bet;
        return true;
    }

    /**
     * @param Player $player
     * @param User $bank
     * @param $multiplier
     */
    public function payPlayer(Player $player, User $bank,  $multiplier)
    {
        if ($multiplier > 0) {
            if ($bank->getBalance() > (1 + $multiplier) * $player->bet) {
                $this->taxationBodyTransact($player, (1 + $multiplier) * $player->bet);
                $player->bet_result = $multiplier * $player->bet;
            } elseif ($bank->getBalance() > abs($player->bet)) {
                $this->Talk->pay_bet_failed_return();
                $this->taxationBodyTransact($player, abs($player->bet));
            } else {
                $this->Talk->pay_bet_failed();
                $player->bet_result = (-1) * $player->bet;
            }
            $player->game_result = new GameResult(GameResult::Win);
        } elseif ($multiplier == 0) {
            if (!$player->free_bet) {
                if ($bank->getBalance() > abs($player->bet)) {
                    $this->taxationBodyTransact($player, abs($player->bet));
                } else {
                    $this->Talk->pay_bet_failed_repay();
                }
            }
            $player->game_result = new GameResult(GameResult::Draw);
        } else {
            if (!$player->free_bet) $player->bet_result = $multiplier * $player->bet;
            $player->game_result = new GameResult(GameResult::Loss);
        }

        $this->Talk->player_result($player, $multiplier);
    }

    /**
     * @param Player $player
     * @param TransactionType $transactionType
     * @return bool
     */
    public function payBank(Player $player, TransactionType $transactionType)
    {
        return $this->Transact->transactToBank(new BankTransaction(
            $player->user,
            $player->bet,
            $transactionType
        ));
    }

    /**
     * @param Player $Player
     * @param $amount
     * @return bool
     */
    public function taxationBodyTransact(Player $Player, $amount)
    {
        return $this->Transact->transactFromBank(new BankTransaction(
            $Player->user,
            $amount,
            new TransactionType(TransactionType::BlackjackWin)
        ));
    }
}