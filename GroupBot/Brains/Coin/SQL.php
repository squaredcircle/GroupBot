<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 26/12/2015
 * Time: 4:15 PM
 */

namespace GroupBot\Brains\Coin;


use GroupBot\Brains\Coin\Types\Transaction;
use GroupBot\Database\DbConnection;
use GroupBot\Types\User;

class SQL extends DbConnection
{
    /**
     * @param $include_bank
     * @return float
     */
    public function getTotalCoinExisting($include_bank)
    {
        $userSQL = new \GroupBot\Database\User($this->db);
        $users = $userSQL->getAllUsers($include_bank);

        $total_coin = 0.0;
        foreach ($users as $i) {
            $total_coin += $i->getBalance(true);
        }
        return $total_coin;
    }

    public function getNumberOfTransactions()
    {
        $sql = 'SELECT id FROM coin_transactions';

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->rowCount();
    }

    public function getNumberOfTransactionsByUser(User $User)
    {
        $sql = 'SELECT id FROM coin_transactions WHERE user_sending = :user_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $User->user_id);
        $query->execute();

        return $query->rowCount();
    }

    /**
     * @param User $User
     * @param $number
     * @return Transaction[]|bool
     */
    public function retrieveRecentLogsByUser(User $User, $number)
    {
        $sql = 'SELECT date, user_sending, user_receiving, amount, type
				FROM coin_transactions
				WHERE user_sending = :user_id OR user_receiving = :user_id
				ORDER BY date DESC
				LIMIT :number';

        $query = $this->db->prepare($sql);
        $query->bindValue(':number', $number, \PDO::PARAM_INT);
        $query->bindValue(':user_id', $User->user_id);
        $query->execute();

        if ($query->rowCount()) {
            return $query->fetchAll(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, 'GroupBot\Brains\Coin\Types\Transaction');
        }
        return false;
    }

    /**
     * @param $number
     * @return Transaction[]|bool
     */
    public function retrieveRecentLogs($number)
    {
        $sql = 'SELECT date, user_sending, user_receiving, amount, type
				FROM coin_transactions
				ORDER BY id DESC
				LIMIT :number';

        $query = $this->db->prepare($sql);
        $query->bindValue(':number', $number, \PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount()) {
            $result = $query->fetchAll();
            $Transactions = array();
            foreach ($result as $transaction) {
                $Transactions[] = new Transaction(
                    $transaction['date'],
                    $transaction['user_sending'],
                    $transaction['user_receiving'],
                    $transaction['amount'],
                    $transaction['type']
                );
            }
            return $Transactions;
        }

        return false;
    }

    public function addTransactionLog(Transaction $Transaction)
    {
        $sql = 'INSERT INTO coin_transactions (user_sending, user_receiving, amount, type)
			  	VALUES (:user_sending, :user_receiving, :amount, :type);';

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_sending', $Transaction->user_sending->user_id);
        $query->bindValue(':user_receiving', $Transaction->user_receiving->user_id);
        $query->bindValue(':amount', $Transaction->amount);
        $query->bindValue(':type', $Transaction->type);

        return $query->execute();
    }

    public function collectPeriodicTax()
    {
        $sql = 'UPDATE users
				SET balance = (balance * ' . (1 - COIN_PERIODIC_TAX) . ')
				WHERE user_name != "' . COIN_TAXATION_BODY . '";';

        $query = $this->db->prepare($sql);
        return $query->execute();
    }
}