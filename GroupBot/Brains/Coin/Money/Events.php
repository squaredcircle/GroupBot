<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 19/11/2015
 * Time: 10:34 PM
 */
namespace GroupBot\Brains\Coin\Money;

use GroupBot\Base\Telegram;
use GroupBot\Brains\Coin\Enums\TransactionType;
use GroupBot\Brains\Coin\Feedback;
use GroupBot\Brains\Coin\Enums\Event;
use GroupBot\Brains\Coin\SQL;
use GroupBot\Brains\Coin\Types\Transaction;

require_once __DIR__ .  '/../../../libraries/common.php';

class Events
{
    private $SQL, $Feedback;
    private $Transact, $Validate;
    private $TaxationBody;

    public function __construct(SQL $SQL, Feedback $Feedback)
    {
        $this->Feedback = $Feedback;
        $this->SQL = $SQL;
        $this->Transact = new Transact($SQL, $Feedback);
        $this->Validate = new Validate($SQL, $Feedback);
        $this->TaxationBody = $SQL->GetUserByName(COIN_TAXATION_BODY);
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
        $total_coin = $this->Validate->getTotalCoinExisting(false);
        $total_users = $this->SQL->GetTotalNumberOfUsers(false);
        $average_coin = $total_coin / $total_users;

        $Users = $this->SQL->GetUsersByBottomBalance($total_users);
        $Users = $this->filterUsersByLastActivity($Users);
        $PoorestUsers = array();
        foreach ($Users as $User) if ($User->getBalance(true) < $average_coin) $PoorestUsers[] = $User;

        $TaxationBody = $this->SQL->GetUserByName(COIN_TAXATION_BODY);
        $to_give = $TaxationBody->getBalance(true) * COIN_POOR_BONUS / count($PoorestUsers);
        $this->Transact->removeMoney($TaxationBody, $TaxationBody->getBalance(true) * COIN_POOR_BONUS);
        foreach ($PoorestUsers as $User) {
            $this->Transact->addMoney($User, $to_give);
        }

        $this->Transact->maintainFixedLevel();

        Telegram::customShitpostingMessage(emoji(0x1F4E2) . " Oh happy day! " . round($TaxationBody->getBalance() * COIN_POOR_BONUS, 2) . " of " . COIN_TAXATION_BODY . "'s wealth has been spread amongst the poorest members of the community.");

        return true;
    }

    private function redistribute()
    {
        $to_collect = COIN_REDISTRIBUTION_TAX * $this->TaxationBody->getBalance(true);
        $users = $this->SQL->GetAllUsers(false);
        $users = $this->filterUsersByLastActivity($users);
        $users = $this->filterUsersByName($users, array(COIN_TAXATION_BODY, "Isaac", "Shlomo"));
        $count = count($users);

        if (!empty($users)) {
            $this->Transact->removeMoney($this->TaxationBody, $to_collect);
            foreach ($users as $i) {
                $this->Transact->addMoney($i, $to_collect / $count);
            }

            $this->SQL->AddTransactionLog(new Transaction(
                NULL,
                $this->TaxationBody,
                NULL,
                $to_collect,
                new TransactionType(TransactionType::RedistributionTax)
            ));

            Telegram::customShitpostingMessage(emoji(0x1F4E2) . COIN_REDISTRIBUTION_BODY . " has redistributed " . round($to_collect, 2) . " of " . COIN_TAXATION_BODY . "'s wealth to the community!");

            $this->Transact->maintainFixedLevel();
            return true;
        }
        return false;
    }

    private function collectPeriodicTax()
    {
        $this->SQL->CollectPeriodicTax();

        $to_collect = COIN_PERIODIC_TAX * $this->Validate->getTotalCoinExisting(false);
        $this->SQL->UpdateUserBalance($this->TaxationBody, $this->TaxationBody->getBalance(true) + $to_collect);

        $this->SQL->AddTransactionLog(new Transaction(
            NULL,
            NULL,
            $this->TaxationBody,
            $to_collect,
            new TransactionType(TransactionType::AllTax)
        ));

        $this->Transact->maintainFixedLevel();

        Telegram::customShitpostingMessage(emoji(0x1F4E2) . " " . COIN_TAXATION_BODY . " just collected " . round($to_collect, 2) . " " . COIN_CURRENCY_NAME . " in tax.");

        return true;
    }
}