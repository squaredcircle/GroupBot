<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 19/11/2015
 * Time: 10:34 PM
 */
namespace GroupBot\Brains\Coin\Money;

use GroupBot\Brains\Coin\Types\BankTransaction;
use GroupBot\Brains\Level\Level;
use GroupBot\Brains\Query;
use GroupBot\Telegram;
use GroupBot\Brains\Coin\Enums\TransactionType;
use GroupBot\Brains\Coin\Feedback;
use GroupBot\Brains\Coin\Enums\Event;
use GroupBot\Brains\Coin\Types\Transaction;
use GroupBot\Types\User;

require_once __DIR__ .  '/../../../Libraries/common.php';

class Events
{
    /** @var \GroupBot\Database\User  */
    private $UserSQL;

    /** @var Feedback  */
    public $Feedback;

    /** @var Transact  */
    public $Transact;

    /** @var Validate  */
    private $Validate;

    public function __construct(\PDO $db)
    {
        $this->Feedback = new Feedback();
        $this->db = $db;
        $this->UserSQL = new \GroupBot\Database\User($db);
        $this->Transact = new Transact($db);
        $this->Validate = new Validate($this->Feedback);
    }

    public function addIncome(User $user)
    {
        if ($user->received_income_today) return false;
        $user->received_income_today = true;

        $status = $this->Transact->transactFromBank(new BankTransaction(
            $user,
            Level::getDailyAllowance($user->level),
            new TransactionType(TransactionType::DailyIncome)
        ));


        return $status;
    }

    private function weightedRandom($array)
    {
        $choices = array_keys($array);
        $weights = array_values($array);

        $count = count($choices);
        $n = 0;
        $num = mt_rand(0, 10000 * array_sum($weights)) / 10000;

        for ($i = 0; $i < $count; $i++) {
            $n += $weights[$i];
            if ($n >= $num) break;
        }
        return $choices[$i];
    }

    public function eventRoulette()
    {
        $choices = array(
            Event::AllTax => COIN_CHANCE_ALL_TAX,
            Event::WealthyTax => COIN_CHANCE_WEALTHY_TAX,
            Event::PoorTax => COIN_CHANCE_POOR_TAX,
            Event::RedistributeTax => COIN_CHANCE_REDISTRIBUTE_TAX,
            Event::RedistributeWealthiest => COIN_CHANCE_REDISTRIBUTE_WEALTHIEST,
            Event::IncreaseValue => COIN_CHANCE_INCREASE_VALUE,
            Event::DecreaseValue => COIN_CHANCE_DECREASE_VALUE,
            Event::RandomBonuses => COIN_CHANCE_RANDOM_BONUS,
            Event::WealthyBonuses => COIN_CHANCE_WEALTH_BONUS,
            Event::PoorBonuses => COIN_CHANCE_POOR_BONUS
        );

        switch ($this->weightedRandom($choices)) {
            case Event::AllTax:
                $this->collectPeriodicTax();
                break;
            case Event::WealthyTax:
                $this->collectPeriodicTax();
                break;
            case Event::PoorTax:
                $this->collectPeriodicTax();
                break;
            case Event::RedistributeTax:
                $this->redistribute();
                break;
            case Event::RedistributeWealthiest:
                $this->redistribute();
                break;
            case Event::IncreaseValue:
                $this->redistribute();
                break;
            case Event::DecreaseValue:
                $this->poorbonuses();
                break;
            case Event::RandomBonuses:
                $this->poorbonuses();
                break;
            case Event::WealthyBonuses:
                $this->poorbonuses();
                break;
            case Event::PoorBonuses:
                $this->poorbonuses();
                break;
        }
    }

    /**
     * @param User[] $users
     * @return User[]
     */
    private function filterUsersByLastActivity($users)
    {
        $out = array();
        foreach ($users as $user) {
            if (isset($user->last_activity)) {
                if (strtotime("+2 weeks") > strtotime($user->last_activity))
                    $out[] = $user;
            }
        }
        return $out;
    }

    /**
     * @param User[] $users
     * @param string[] $names
     * @return User[]
     */
    private function filterUsersByName($users, $names)
    {
        $out = array();
        foreach ($users as $user) {
            if (!in_array($user->user_name, $names)) {
                $out[] = $user;
            }
        }
        return $out;
    }

    private function poorbonuses()
    {
        $total_coin = $this->Transact->CoinSQL->getTotalCoinExisting(false);
        $total_users = $this->UserSQL->GetTotalNumberOfUsers(false);
        $average_coin = $total_coin / $total_users;

        $Users = Query::getUsersByMoneyAndLevel($this->db, NULL, false, false);
        $Users = $this->filterUsersByLastActivity($Users);

        /** @var User[] $PoorestUsers */
        $PoorestUsers = array();
        foreach ($Users as $User) if ($User->getBalance(true) < $average_coin) $PoorestUsers[] = $User;

        $bank = $this->UserSQL->getUserFromId(COIN_BANK_ID);
        $to_give = $bank->getBalance(true) * COIN_POOR_BONUS / count($PoorestUsers);
        $this->Transact->removeMoney($bank, $bank->getBalance(true) * COIN_POOR_BONUS);
        foreach ($PoorestUsers as $User) {
            $this->Transact->addMoney($User, $to_give);
            $User->save($this->db);
        }

        $this->Transact->maintainFixedLevel($bank);
        $bank->save($this->db);

        Telegram::customShitpostingMessage(emoji(0x1F4E2) . " Oh happy day! " . round($bank->getBalance() * COIN_POOR_BONUS, 2) . " of " . COIN_TAXATION_BODY . "'s wealth has been spread amongst the poorest members of the community.");

        return true;
    }

    private function redistribute()
    {
        $bank = $this->UserSQL->getUserFromId(COIN_BANK_ID);
        $to_collect = COIN_REDISTRIBUTION_TAX * $bank->getBalance(true);
        $users = $this->UserSQL->GetAllUsers(false);
        $users = $this->filterUsersByLastActivity($users);
        $users = $this->filterUsersByName($users, array(COIN_TAXATION_BODY, "Isaac", "Shlomo"));
        $count = count($users);

        if (!empty($users)) {
            $this->Transact->removeMoney($bank, $to_collect);
            foreach ($users as $i) {
                $this->Transact->addMoney($i, $to_collect / $count);
                $i->save($this->db);
            }

            $this->Transact->CoinSQL->AddTransactionLog(new Transaction(
                $bank,
                NULL,
                $to_collect,
                new TransactionType(TransactionType::RedistributionTax)
            ));

            Telegram::customShitpostingMessage(emoji(0x1F4E2) . COIN_REDISTRIBUTION_BODY . " has redistributed " . round($to_collect, 2) . " of " . COIN_TAXATION_BODY . "'s wealth to the community!");

            $this->Transact->maintainFixedLevel($bank);
            $bank->save($this->db);
            return true;
        }
        return false;
    }

    private function collectPeriodicTax()
    {
        $bank = $this->UserSQL->getUserFromId(COIN_BANK_ID);
        $this->Transact->CoinSQL->CollectPeriodicTax();

        $to_collect = COIN_PERIODIC_TAX * $this->Transact->CoinSQL->getTotalCoinExisting(false);
        $bank->balance = $bank->getBalance(true) + $to_collect;

        $this->Transact->CoinSQL->AddTransactionLog(new Transaction(
            NULL,
            $bank,
            $to_collect,
            new TransactionType(TransactionType::AllTax)
        ));

        $this->Transact->maintainFixedLevel($bank);
        $bank->save($this->db);

        Telegram::customShitpostingMessage(emoji(0x1F4E2) . " " . COIN_TAXATION_BODY . " just collected " . round($to_collect, 2) . " " . COIN_CURRENCY_NAME . " in tax.");

        return true;
    }
}