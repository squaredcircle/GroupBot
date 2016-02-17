<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 13/02/2016
 * Time: 12:58 AM
 */

namespace GroupBot\Brains\CardGame;


use GroupBot\Base\DbControl;
use GroupBot\Brains\CardGame\Enums\GameResult;
use GroupBot\Brains\CardGame\Enums\GameType;
use GroupBot\Brains\CardGame\Types\Game;
use GroupBot\Brains\CardGame\Types\Player;
use GroupBot\Brains\Coin\Coin;
use GroupBot\Brains\Coin\Enums\TransactionType;
use GroupBot\Brains\Coin\Types\Transaction;
use GroupBot\libraries\eos\Parser;

class Bets
{
    private $Coin, $db, $Talk;
    public $bet, $free_bet;

    public function __construct(Talk $Talk)
    {
        $this->free_bet = false;
        $this->Talk = $Talk;
        $this->Coin = new Coin();
        $DbControl = new DbControl();
        $this->db = $DbControl->getObject();
    }

    /**
     * @param $user_id
     * @return int
     */
    private function getFreeBetsToday($user_id)
    {
        $sql = 'SELECT free_bets_today FROM casino WHERE user_id = :user_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $user_id);

        $query->execute();

        if ($query->rowCount()) {
            return $query->fetch()['free_bets_today'];
        }
        return 0;
    }

    private function incrementFreeBetsToday($user_id, $free_bets_today)
    {
        $sql = 'INSERT INTO casino (user_id, free_bets_today)
                VALUES (:user_id, :free_bets_today)
                ON DUPLICATE KEY UPDATE
                  free_bets_today = free_bets_today + 1';

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $user_id);
        $query->bindValue(':free_bets_today', $free_bets_today);

        return $query->execute();
    }

    /**
     * @param Game $game
     * @param $user_id
     * @param $bet
     * @return bool
     */
    public function checkPlayerBet(Game $game, $user_id, $bet)
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
        $balance = $this->Coin->SQL->GetUserById($user_id)->getBalance();
        $TaxationBody = $this->Coin->SQL->GetUserByName(COIN_TAXATION_BODY);

        if (!(is_numeric($this->bet) && $this->bet >= 0 && $this->bet == round($this->bet, 2))) {
            if (stripos($this->bet, "all") !== false) {
                try {
                    $value = Parser::solve($this->bet, array('all' => $balance));
                    $value = round($value,2);
                    if ($value >=0 && $value <= $balance) {
                        $this->bet = $value;
                        $this->Talk->bet_calculation($value);
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

        if ($balance < 1 && $this->bet <= 1) {
            if ($TaxationBody->getBalance() > $game->betting_pool + $max_bet_factor) {
                $free_bets_today = $this->getFreeBetsToday($user_id);
                if ($free_bets_today < CASINO_DAILY_FREE_BETS) {
                    $this->Talk->bet_free();
                    $this->bet = 1;
                    $this->free_bet = true;
                    $this->incrementFreeBetsToday($user_id, $free_bets_today + 1);
                } else {
                    $this->Talk->bet_free_too_many();
                    return false;
                }
            } else {
                $this->Talk->bet_free_failed();
                return false;
            }
        } elseif ($this->bet < 1) {
            if ($TaxationBody->getBalance() > $game->betting_pool + $max_bet_factor) {
                $this->bet = 1;
                $this->Talk->bet_mandatory();
            } else {
                $this->bet = 0;
                $this->Talk->bet_mandatory_failed();
            }
            return true;
        } elseif ($this->bet > $balance) {
            $this->Talk->bet_too_high($balance);
            return false;
        }

        if ($TaxationBody->getBalance() < $game->betting_pool + $max_bet_factor * $this->bet) {
            $this->Talk->bet_too_high_for_dealer();
            return false;
        }

        $game->betting_pool += $this->bet;
        return true;
    }

    /**
     * @param Player $player
     * @param $bet
     * @param $multiplier
     */
    public function payPlayer(Player $player, $multiplier)
    {
        $TaxationBody = $this->Coin->SQL->GetUserByName(COIN_TAXATION_BODY);

        if ($multiplier > 0) {
            if ($TaxationBody->getBalance() > (1 + $multiplier) * $player->bet) {
                $this->taxationBodyTransact($player, (1 + $multiplier) * $player->bet);
                $player->bet_result = $multiplier * $player->bet;
            } elseif ($TaxationBody->getBalance() > abs($player->bet)) {
                $this->Talk->pay_bet_failed_return();
                $this->taxationBodyTransact($player, abs($player->bet));
            } else {
                $this->Talk->pay_bet_failed();
                $player->bet_result = (-1) * $player->bet;
            }
            $player->game_result = new GameResult(GameResult::Win);
        } elseif ($multiplier == 0) {
            if (!$player->free_bet) {
                if ($TaxationBody->getBalance() > abs($player->bet)) {
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
     * @param Player $Player
     * @param $amount
     * @return bool
     */
    public function taxationBodyTransact(Player $Player, $amount)
    {
        $TaxationBody = $this->Coin->SQL->GetUserByName(COIN_TAXATION_BODY);

        return $this->Coin->Transact->performTransaction(new Transaction(
            NULL,
            $TaxationBody,
            $this->Coin->SQL->GetUserById($Player->user_id),
            $amount,
            new TransactionType(TransactionType::BlackjackWin)
        ));
    }
}