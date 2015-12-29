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
        $num = mt_rand(0, array_sum($weights));

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
                break;
            case Event::WealthyTax:
                break;
            case Event::PoorTax:
                break;
            case Event::RedistributeTax:
                break;
            case Event::RedistributeWealthiest:
                break;
            case Event::IncreaseValue:
                break;
            case Event::DecreaseValue:
                break;
            case Event::RandomBonuses:
                break;
            case Event::WealthyBonuses:
                break;
            case Event::PoorBonuses:
                break;
        }

        $this->poorbonuses();
    }

    private function poorbonuses()
    {
        $total_coin = $this->Validate->getTotalCoinExisting(false);
        $total_users = $this->SQL->GetTotalNumberOfUsers(false);
        $average_coin = $total_coin / $total_users;

        $Users = $this->SQL->GetUsersByBottomBalance($total_users);
        $PoorestUsers = array();
        foreach ($Users as $User) if ($User->balance < $average_coin) $PoorestUsers[] = $User;

        $TaxationBody = $this->SQL->GetUserByName(COIN_TAXATION_BODY);
        $to_give = $TaxationBody->balance * COIN_POOR_BONUS / count($PoorestUsers);
        $this->Transact->removeMoney($TaxationBody, $TaxationBody->balance * COIN_POOR_BONUS);
        foreach ($PoorestUsers as $User) {
            $this->Transact->addMoney($User, $to_give);
        }

        $Telegram = new Telegram();
        $Telegram->customShitpostingMessage(emoji(0x1F4E2) . "Oh happy day! " . round($TaxationBody->balance * COIN_POOR_BONUS, 2) . " of " . COIN_TAXATION_BODY . "'s wealth has been spread amongst the poorest members of the community.");

        return true;
    }

    private function redistribute()
    {
        $to_collect = COIN_REDISTRIBUTION_TAX * $this->TaxationBody->balance;
        $leaderboard = $this->SQL->GetUsersByTopBalance(10);
        $exclude_list = [COIN_TAXATION_BODY, "Isaac", "Shlomo", "Grail"];
        $count = 0;

        if (!empty($leaderboard)) {
            foreach ($leaderboard as $i) {
                if (!in_array($i['user_name'], $exclude_list)) $count++;
            }
            if ($count > 0)
            {
                $this->Transact->removeMoney($this->TaxationBody, $to_collect);
                foreach ($leaderboard as $i) {
                    if (!in_array($i['user_name'], $exclude_list))
                        $this->Transact->addMoney($i, $to_collect / $count);
                }

                $this->SQL->AddTransactionLog(new Transaction(
                    NULL,
                    $this->TaxationBody,
                    NULL,
                    $to_collect,
                    new TransactionType(TransactionType::RedistributionTax)
                ));

                $Telegram = new Telegram();
                $Telegram->customShitpostingMessage(emoji(0x1F4E2) . COIN_REDISTRIBUTION_BODY . " has redistributed " . round($to_collect, 2) . " of " . COIN_TAXATION_BODY . "'s wealth to the community!");

                $this->Transact->maintainFixedLevel();
                return true;
            }
        }
        return false;
    }

    private function collectPeriodicTax()
    {
        $this->SQL->CollectPeriodicTax();

        $to_collect = COIN_PERIODIC_TAX * $this->Validate->getTotalCoinExisting(false);
        $this->SQL->UpdateUserBalance($this->TaxationBody, $this->TaxationBody->balance + $to_collect);

        $this->SQL->AddTransactionLog(new Transaction(
            NULL,
            NULL,
            $this->TaxationBody,
            $to_collect,
            new TransactionType(TransactionType::AllTax)
        ));

        $this->Transact->maintainFixedLevel();

        $Telegram = new Telegram();
        $Telegram->customShitpostingMessage(emoji(0x1F4E2) . " " . COIN_TAXATION_BODY . " just collected " . round($to_collect, 2) . " " . CURRENCY_NAME . " in tax.");

        return true;
    }
}