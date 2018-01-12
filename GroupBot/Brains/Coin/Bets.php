<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/09/2016
 * Time: 10:29 PM
 */

namespace GroupBot\Brains\Coin;


use GroupBot\Brains\Coin\Enums\TransactionType;
use GroupBot\Brains\Coin\Money\Transact;
use GroupBot\Brains\Coin\Types\BankTransaction;
use GroupBot\Libraries\eos\Parser;
use GroupBot\Types\User;

class Bets
{
    private $Transact, $db;
    public $free_bet, $response;

    public function __construct(\PDO $db)
    {
        $this->Transact = new Transact($db);
        $this->db = $db;
        $this->response = '';
    }

    /**
     * @param User $user
     * @param $bet
     * @param $betting_pool
     * @param $max_bet_factor
     * @return double|bool
     */
    public function checkPlayerBet(User $user, $bet, $betting_pool, $max_bet_factor)
    {
        $DbUser = new \GroupBot\Database\User($this->db);
        $bank = $DbUser->getUserFromId(COIN_BANK_ID);

        if (!(is_numeric($bet) && $bet >= 0 && $bet == round($bet, 2)))
        {
            if (stripos($bet, "all") !== false)
            {
                try
                {
                    $value = Parser::solve($bet, array('all' => $user->getBalance(true)));
                    $value = round($value, 4);
                    if ($value >= 0 && $value <= $user->getBalance(true))
                    {
                        $bet = $value;
                        $this->bet_calculation(round($value, 2));
                    }
                    else
                    {
                        $this->bet_invalid_calculation();
                        return false;
                    }
                }
                catch (\Exception $e)
                {
                    $this->bet_invalid_notation();
                    return false;
                }
            }
            else
            {
                $this->bet_invalid();
                return false;
            }
        }

        if ($bet > CASINO_BETTING_MAX)
        {
            $bet = CASINO_BETTING_MAX;
            $this->bet_limit();
        }

        if ($user->getBalance(true) < 1 && $bet <= 1)
        {
            if ($bank->getBalance() > $betting_pool + $max_bet_factor)
            {
                if ($user->free_bets_today < CASINO_DAILY_FREE_BETS)
                {
                    $this->bet_free($user->free_bets_today);
                    $bet = 1;
                    $this->free_bet = true;
                    $user->free_bets_today++;
                    $user->save($this->db);
                }
                else
                {
                    $this->bet_free_too_many();
                    return false;
                }
            }
            else
            {
                $this->bet_free_failed();
                return false;
            }
        }
        elseif ($bet < 1)
        {
            if ($bank->getBalance() > $betting_pool + $max_bet_factor)
            {
                $bet = 1.0;
                $this->bet_mandatory();
            }
            else
            {
                $bet = 0.0;
                $this->bet_mandatory_failed();
            }
            return $bet;
        }
        elseif ($bet > $user->getBalance(true))
        {
            $this->bet_too_high($user->getBalance(true));
            return false;
        }

        if ($bank->getBalance() < $betting_pool + $max_bet_factor * $bet)
        {
            $this->bet_too_high_for_dealer();
            return false;
        }

        return $bet;
    }

    /**
     * @param User $user
     * @param User $bank
     * @param $bet
     * @param $multiplier
     * @param bool $free_bet
     */
    public function payUser(User $user, User $bank, $bet, $multiplier, $free_bet = false)
    {
        if ($multiplier > 0)
        {
            if ($bank->getBalance() > (1 + $multiplier) * $bet)
            {
                $this->taxationBodyTransact($user, (1 + $multiplier) * $bet);
            }
            elseif ($bank->getBalance() > abs($bet))
            {
                $this->pay_bet_failed_return();
                $this->taxationBodyTransact($user, abs($bet));
            }
            else
            {
                $this->pay_bet_failed();
            }
        }
        elseif (!$free_bet)
        {
            if ($bank->getBalance() > abs($bet))
            {
                $this->taxationBodyTransact($user, abs($bet));
            }
            else
            {
                $this->pay_bet_failed_repay();
            }
        }

        $this->player_result($user, $bet, $multiplier, $free_bet);
    }

    /**
     * @param User $user
     * @param $amount
     * @param TransactionType $transactionType
     * @return bool
     */
    public function payBank(User $user, $amount, TransactionType $transactionType)
    {
        return $this->Transact->transactToBank(new BankTransaction(
            $user,
            $amount,
            $transactionType
        ));
    }

    /**
     * @param User $user
     * @param $amount
     * @return bool
     */
    public function taxationBodyTransact(User $user, $amount)
    {
        return $this->Transact->transactFromBank(new BankTransaction(
            $user,
            $amount,
            new TransactionType(TransactionType::BlackjackWin)
        ));
    }

    private function addMessage($msg)
    {
        $this->response .= $msg;
    }

    private function bet_invalid()
    {
        $this->addMessage("👎 Please enter a valid bet.");
    }

    private function bet_invalid_notation()
    {
        $this->addMessage("👎 Your bet doesn't make sense. You can use the word `all` in a sensible equation to calculate your bet.");
    }

    private function bet_invalid_calculation()
    {
        $this->addMessage("👎 Sorry, that calculates to an amount you cannot bet.");
    }

    private function bet_mandatory()
    {
        $this->addMessage("📝 You are betting with the mandatory bet of 1 Coin.");
    }

    private function bet_mandatory_failed()
    {
        $this->addMessage("📝 " . COIN_TAXATION_BODY . " can't accept the mandatory bet of 1 Coin right now. You are betting 0 Coin.");
    }

    private function bet_limit()
    {
        $this->addMessage("👉 The betting limit per game is " . CASINO_BETTING_MAX . ". Your bet has been adjusted.");
    }

    private function bet_too_high($balance)
    {
        $out = "👎 You don't have that much Coin to bet.";
        if ($balance < 1)
        {
            $out .= "\nHowever, " . COIN_TAXATION_BODY . " can give you a free bet of 1 Coin if you wish.";
        }
        $this->addMessage($out);
    }

    private function bet_too_high_for_dealer()
    {
        $this->addMessage("👎 " . COIN_TAXATION_BODY . " can't accept a bet that high right now.");
    }

    private function bet_calculation($value)
    {
        $this->addMessage("📝 Okay, you've placed a bet of " . $value . " Coin.");
    }

    private function bet_free($free_bets_today)
    {
        $this->addMessage("💉 " . COIN_TAXATION_BODY . " has given you a free bet of 💰1 (*" . (CASINO_DAILY_FREE_BETS - $free_bets_today - 1) . "* left today). Welcome back!");
    }

    private function bet_free_failed()
    {
        $this->addMessage("👎 " . COIN_TAXATION_BODY . " isn't able to give you a free bet at the moment, sorry.");
    }

    private function bet_free_too_many()
    {
        $now = new \DateTime();
        $future_date = new \DateTime('tomorrow');
        $interval = $future_date->diff($now);
        $time = $interval->format("*%h hours* and *%i minutes*");

        $this->addMessage("👎 Sorry - you only get " . CASINO_DAILY_FREE_BETS . " free bets per day. Come back tomorrow!\n($time to go)");
    }

    private function pay_bet_failed_return()
    {
        $this->addMessage("👎 " . COIN_TAXATION_BODY . " doesn't have enough money to pay you, but it can at least return your bet.");
    }

    private function pay_bet_failed()
    {
        $this->addMessage("👎 " . COIN_TAXATION_BODY . " doesn't have enough money to pay you, fam...\nsorry.");
    }

    private function pay_bet_failed_repay()
    {
        $this->addMessage("👎 " . COIN_TAXATION_BODY . " doesn't have enough money to repay you, fam...\nsorry.");
    }

    public function player_result(User $user, $bet, $multiplier, $free_bet)
    {
        if ($multiplier > 0)
        {
            $out = "💰 *" . $user->getName() . "* wins " . ($multiplier * $bet + 0) . " coin!";
        }
        elseif ($multiplier == 0)
        {
            if ($free_bet)
            {
                $out = "💰 *" . $user->getName() . "* cannot regain a free bet";
            }
            else
            {
                $out = "💰 *" . $user->getName() . "* regains their bet of " . ($bet + 0);
            }
        }
        elseif ($multiplier < 0)
        {
            $free = $free_bet ? " free " : " ";
            $out = "💰 *" . $user->getName() . "* loses their" . $free . "bet of " . ($bet + 0);
        }

        $out .= " (`" . $user->getBalance() . "`)";
        $this->addMessage($out);
    }
}