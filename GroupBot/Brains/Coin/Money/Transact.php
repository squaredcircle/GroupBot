<?php

namespace GroupBot\Brains\Coin\Money;

use GroupBot\Brains\Coin\CoinSQL;
use GroupBot\Brains\Coin\Enums\TransactionType;
use GroupBot\Brains\Coin\Feedback;
use GroupBot\Brains\Coin\SQL;
use GroupBot\Brains\Coin\Types\BankTransaction;
use GroupBot\Brains\Coin\Types\Transaction;
use GroupBot\Types\User;

class Transact
{
    /** @var Validate  */
	public $Validate;

    /** @var Feedback  */
    public $Feedback;

    /** @var \PDO  */
    private $db;

    /** @var SQL  */
    public $CoinSQL;

    /** @var \GroupBot\Database\User */
    public $UserSQL;

    public function __construct(\PDO $db)
    {
		$this->db = $db;
        $this->CoinSQL = new SQL($db);
		$this->Feedback = new Feedback();
		$this->Validate = new Validate($this->Feedback);

        $this->UserSQL = new \GroupBot\Database\User($this->db);
    }

	public function transactToBank(BankTransaction $transaction)
	{
		$trans = new Transaction(
			$transaction->user,
            $this->UserSQL->getUserFromId(COIN_BANK_ID),
			$transaction->amount,
			$transaction->type,
			$transaction->date
		);
		return $this->performTransaction($trans);
	}

	public function transactFromBank(BankTransaction $transaction)
	{
		$trans = new Transaction(
            $this->UserSQL->getUserFromId(COIN_BANK_ID),
			$transaction->user,
			$transaction->amount,
			$transaction->type,
			$transaction->date
		);
		return $this->performTransaction($trans);
	}

	public function performTransaction(Transaction $Transaction)
	{
		if ($this->Validate->checkTransaction($Transaction))
		{
			$Transaction = $this->Validate->parseTransaction($Transaction);
			$status = $this->transferMoney($Transaction);

            $Transaction->user_receiving->save($this->db);
            $Transaction->user_sending->save($this->db);
			return $status;
		}
		return false;
	}

	private function transferMoney(Transaction $Transaction)
	{
        $amount_adj = $this->calculateTransactionTax($Transaction);
        $this->db->beginTransaction();

		if ($this->removeMoney($Transaction->user_sending, $Transaction->amount) && $this->addMoney($Transaction->user_receiving, $amount_adj)) {
            $bank = $this->getBank($Transaction);
            if ($Transaction->user_sending->user_id != $bank->user_id) $this->payTransactionTax($Transaction, $bank);
            $this->maintainFixedLevel($bank);

			$Transaction->amount = $amount_adj;
			$this->Feedback->addFeedbackCode(24); // Coin transferred.
			$this->CoinSQL->AddTransactionLog($Transaction);
			$this->updateUserLastActivity($Transaction);
            $this->db->commit();
			return $Transaction->amount;
		}
        $this->db->rollBack();
        return false;
	}

    private function getBank(Transaction $transaction)
    {
        if ($transaction->user_sending->user_id == COIN_BANK_ID) {
            return $transaction->user_sending;
        } elseif ($transaction->user_receiving->user_id == COIN_BANK_ID) {
            return $transaction->user_receiving;
        }
        return $this->UserSQL->getUserFromId(COIN_BANK_ID);
    }

	public function addMoney(User $User, $amount)
	{
		$User->balance = $User->getBalance(true) + $amount;
		return true;
	}

    public function removeMoney(User $User, $amount)
	{
        $new_balance = $User->getBalance(true) - $amount;
		
		if ($new_balance >= 0) {
			$User->balance = $new_balance;
			return true;
		} else {
		    if ($User->user_id == COIN_BANK_ID) {
                $this->Feedback->addFeedbackCode(30);
            }
		    else {
                $this->Feedback->addFeedbackCode(27); // You don't have enough Coin!
            }
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

	private function payTransactionTax(Transaction $Transaction, User $bank)
	{
		if (($Transaction->user_sending->user_id != COIN_BANK_ID) && ($Transaction->user_receiving->user_id != COIN_BANK_ID)) {
			$this->addMoney($bank, COIN_TRANSACTION_TAX * $Transaction->amount);
			$this->CoinSQL->AddTransactionLog(new Transaction(
				$Transaction->user_sending,
				$bank,
				COIN_TRANSACTION_TAX * $Transaction->amount,
				new TransactionType(TransactionType::TransactionTax)
			));
		}
	}

	public function maintainFixedLevel(User $bank)
	{
		$total_money = $this->CoinSQL->getTotalCoinExisting(true);
		$total_money_target = 100000;

		if ($total_money < $total_money_target)
		{
			$to_give = $total_money_target - $total_money;
			$this->addMoney($bank, $to_give);
            $bank->save($this->db);
		}
		elseif ($total_money > $total_money_target)
		{
			$to_take = $total_money - $total_money_target;
			$this->removeMoney($bank, $to_take);
            $bank->save($this->db);
		}
	}

	private function updateUserLastActivity(Transaction $transaction)
	{
		if ($transaction->type == TransactionType::BlackjackBet ||
			$transaction->type == TransactionType::BlackjackWin ||
			$transaction->type == TransactionType::CasinoWarBet ||
			$transaction->type == TransactionType::CasinoWarWin)
		{
			$date = date("Y-m-d H:i:s");
			$transaction->user_sending->last_activity = $date;
			$transaction->user_receiving->last_activity = $date;
		} elseif ($transaction->type == TransactionType::Manual) {
			$date = date("Y-m-d H:i:s");
			$transaction->user_sending->last_activity = $date;
		}
	}
}
