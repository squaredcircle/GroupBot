<?php

namespace GroupBot\Brains\Coin\Money;

use GroupBot\Brains\Coin\Enums\TransactionType;
use GroupBot\Brains\Coin\Feedback;
use GroupBot\Brains\Coin\SQL;
use GroupBot\Brains\Coin\Types\CoinUser;
use GroupBot\Brains\Coin\Types\Transaction;

class Transact
{
	public $Validate;
	private $SQL, $Feedback;
	
    public function __construct(SQL $SQL, Feedback $Feedback)
    {
		$this->Feedback = $Feedback;
		$this->SQL = $SQL;
		$this->Validate = new Validate($SQL, $Feedback);
    }

	public function performTransaction(Transaction $Transaction)
	{
		if ($this->Validate->checkTransaction($Transaction))
		{
			$Transaction = $this->Validate->parseTransaction($Transaction);
			$this->transferMoney($Transaction);
			return true;
		}
		return false;
	}

	private function transferMoney(Transaction $Transaction)
	{
        $amount_adj = $this->calculateTransactionTax($Transaction);

		if ($this->removeMoney($Transaction->user_sending, $Transaction->amount) && $this->addMoney($Transaction->user_receiving, $amount_adj)) {
			$this->payTransactionTax($Transaction);
			$this->maintainFixedLevel();

			$Transaction->amount = $amount_adj;
			$this->Feedback->addFeedbackCode(24); // Coin transferred.
			$this->SQL->AddTransactionLog($Transaction);
			return $Transaction->amount;
		}
        $this->Feedback->addFeedbackCode(25); // Transfer botched. Oops.
        return false;
	}

	public function addMoney(CoinUser $User, $amount)
	{
		return $this->SQL->UpdateUserBalance($User, $User->balance + $amount);
	}

    public function removeMoney(CoinUser $User, $amount)
	{
        $new_balance = $User->getBalance(true) - $amount;
		
		if ($new_balance >= 0) {
			return $this->SQL->UpdateUserBalance($User, $new_balance);
		} else {
			$this->Feedback->addFeedbackCode(27); // You don't have enough Coin!
			return false;
		}
	}

	private function calculateTransactionTax(Transaction $Transaction)
	{
		if (($Transaction->user_sending->user_name != COIN_TAXATION_BODY) && ($Transaction->user_receiving->user_name != COIN_TAXATION_BODY)) {
			return (1-COIN_TRANSACTION_TAX) * $Transaction->amount;
		} else {
			return $Transaction->amount;
		}
	}

	private function payTransactionTax(Transaction $Transaction)
	{
		$TaxationBody = $this->SQL->GetUserByName(COIN_TAXATION_BODY);
		if (($Transaction->user_sending->user_name != COIN_TAXATION_BODY) && ($Transaction->user_receiving->user_name != COIN_TAXATION_BODY)) {
			$this->addMoney($TaxationBody, COIN_TRANSACTION_TAX * $Transaction->amount);
			$this->SQL->AddTransactionLog(new Transaction(
				NULL,
				$Transaction->user_sending,
				$TaxationBody,
				COIN_TRANSACTION_TAX * $Transaction->amount,
				new TransactionType(TransactionType::TransactionTax)
			));
		}
	}

	public function maintainFixedLevel()
	{
		$TaxationBody = $this->SQL->GetUserByName(COIN_TAXATION_BODY);
		$total_money = $this->Validate->getTotalCoinExisting(true);

		if ($total_money < 1000)
		{
			$to_give = 1000 - $total_money;
			$this->addMoney($TaxationBody, $to_give);
		}
		elseif ($total_money > 1000)
		{
			$to_take = $total_money - 1000;
			$this->removeMoney($TaxationBody, $to_take);
		}
	}
}