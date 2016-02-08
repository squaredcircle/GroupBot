<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 25/12/2015
 * Time: 11:52 PM
 */

namespace GroupBot\Brains\Coin\Money;


use GroupBot\Brains\Coin\Feedback;
use GroupBot\Brains\Coin\SQL;
use GroupBot\Brains\Coin\Types\CoinUser;
use GroupBot\Brains\Coin\Types\Transaction;

class Validate
{
    private $Feedback, $SQL;

    public function __construct(SQL $SQL, Feedback $Feedback)
    {
        $this->Feedback = $Feedback;
        $this->SQL = $SQL;
    }

    public function checkTransaction(Transaction $Transaction)
    {
        return ($this->checkAmount($Transaction->amount) &&
                $this->checkRecipientAndSender($Transaction->user_receiving, $Transaction->user_sending)
        );
    }

    public function parseTransaction(Transaction $Transaction)
    {
        $Transaction->amount = $this->parseAmount($Transaction->user_sending, $Transaction->amount);
        return $Transaction;
    }

    private function checkAmount($amount)
    {
        if (is_numeric($amount) && $amount + 0 > 0 && $amount >= 0.01) {
            return true;
        } else {
            $this->Feedback->addFeedbackCode(20); // Invalid amount entered.
        }
        return false;
    }

    private function checkRecipientAndSender(CoinUser $recipient, CoinUser $sender)
    {
        if (isset($sender->user_id) && $recipient->user_id == $sender->user_id) {
            $this->Feedback->addFeedbackCode(21); // Can't send Coin to yourself!
            return false;
        }

        return true;
    }

    private function parseAmount(CoinUser $User, $amount)
    {
        if ($User->getBalance() - $amount == 0)
            return $User->getBalance(true);
        else
            return $amount;
    }

    public function getTotalCoinExisting($include_taxation_body)
    {
        $users = $this->SQL->GetAllUsers($include_taxation_body);

        $total_coin = 0.0;
        foreach ($users as $i) {
            $total_coin += $i->getBalance(true);
        }
        return $total_coin;
    }
}