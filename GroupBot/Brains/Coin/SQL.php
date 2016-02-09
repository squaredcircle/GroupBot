<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 26/12/2015
 * Time: 4:15 PM
 */

namespace GroupBot\Brains\Coin;


use GroupBot\Base\DbControl;
use GroupBot\Brains\Coin\Types\CoinUser;
use GroupBot\Brains\Coin\Types\Transaction;

class SQL
{
    private $db, $Feedback;

    public function __construct(Feedback $Feedback)
    {
        $DbControl = new DbControl();
        $this->db = $DbControl->getObject();
        $this->Feedback = $Feedback;
    }

    public function CreateNewUser(CoinUser $User)
    {
        if ($this->DoesUserExistById($User->user_id)) {
            $this->Feedback->addFeedbackCode(33); // You've already got a coin account
        } elseif ($this->DoesUserExistByName($User->user_name)) {
            $this->Feedback->addFeedbackCode(14); // Username taken
        } else {
            $sql = 'INSERT INTO coin_users (user_id, user_name, balance, last_activity)
                    VALUES(:user_id, :user_name, :balance, :last_activity)';
            $query = $this->db->prepare($sql);
            $query->bindValue(':user_id', $User->user_id);
            $query->bindValue(':user_name', $User->user_name);
            $query->bindValue(':balance', $User->getBalance(true));
            $query->bindValue(':last_activity', $User->last_activity);

            if ($query->execute()) {
                $this->Feedback->addFeedback("Hi " . $User->user_name . "! Your " . COIN_CURRENCY_NAME
                    . " has been set up; you've got " . $User->getBalance(). " " . COIN_CURRENCY_NAME . " at the moment.");
                return true;
            } else {
                $this->Feedback->addFeedback("Something went wrong; I couldn't set up an " . COIN_CURRENCY_NAME . " account for you, " . $User->user_name . "...");
            }
        }
        return false;
    }

    public function UpdateUserBalance(CoinUser $User, $new_balance)
    {
        $sql = 'UPDATE coin_users
				SET balance = :balance
				WHERE user_id = :user_id OR user_name = :user_name';

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $User->user_id);
        $query->bindValue(':user_name', $User->user_name);
        $query->bindValue(':balance', $new_balance);

        return $query->execute();
    }

    public function UpdateUserLastActivity(CoinUser $User, $new_date)
    {
        $sql = 'UPDATE coin_users
				SET last_activity = :last_activity
				WHERE user_id = :user_id OR user_name = :user_name';

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $User->user_id);
        $query->bindValue(':user_name', $User->user_name);
        $query->bindValue(':last_activity', $new_date);

        return $query->execute();
    }

    public function DoesUserExistById($user_id)
    {
        $sql = 'SELECT id FROM coin_users WHERE user_id = :user_id LIMIT 1';

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $user_id);
        $query->execute();

        $result_row = $query->fetchObject();

        return $result_row ? true : false;
    }

    public function DoesUserExistByName($user_name)
    {
        $sql = 'SELECT id FROM coin_users WHERE user_name = :user_name LIMIT 1';

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_name', $user_name);
        $query->execute();

        $result_row = $query->fetchObject();

        return $result_row ? true : false;
    }

    public function GetUserById($user_id)
    {
        $sql = 'SELECT user_id, user_name, balance, last_activity
                FROM coin_users WHERE user_id = :user_id
                LIMIT 1';
        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $user_id);
        $query->execute();

        $result_row = $query->fetch();
        if ($result_row) {
            return new CoinUser(
                $result_row['user_id'],
                $result_row['user_name'],
                $result_row['balance'],
                $result_row['last_activity']
            );
        }

        return false;
    }

    public function GetUserByName($user_name)
    {
        $sql = 'SELECT user_id, user_name, balance, last_activity
                FROM coin_users WHERE user_name = :user_name
                LIMIT 1';
        $query = $this->db->prepare($sql);
        $query->bindValue(':user_name', $user_name);
        $query->execute();

        $result_row = $query->fetch();
        if ($result_row) {
            return new CoinUser(
                $result_row['user_id'],
                $result_row['user_name'],
                $result_row['balance'],
                $result_row['last_activity']
            );
        }

        return false;
    }

    public function GetAllUsers($include_taxation_body)
    {
        if ($include_taxation_body)
            $sql = 'SELECT user_id, user_name, balance, last_activity FROM coin_users';
        else
            $sql = 'SELECT user_id, user_name, balance, last_activity FROM coin_users WHERE user_name != "'. COIN_TAXATION_BODY . '"';

        $query = $this->db->prepare($sql);
        $query->execute();

        if ($query->rowCount()) {
            $Users = array();
            foreach ($query->fetchAll() as $user) {
                $Users[] = new CoinUser(
                    $user['user_id'],
                    $user['user_name'],
                    $user['balance'],
                    $user['last_activity']
                );
            }
            return $Users;
        }
        return false;
    }

    public function GetTotalNumberOfUsers($include_taxation_body)
    {
        if ($include_taxation_body)
            $sql = 'SELECT id FROM coin_users';
        else
            $sql = 'SELECT id FROM coin_users WHERE user_name != "'. COIN_TAXATION_BODY . '"';

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->rowCount();
    }

    public function GetUsersByTopBalance($number)
    {
        $sql = 'SELECT user_id, user_name, balance, last_activity
				FROM coin_users
				ORDER BY balance DESC
				LIMIT :number';

        $query = $this->db->prepare($sql);
        $query->bindValue(':number', $number, \PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount()) {
            $Users = array();
            foreach ($query->fetchAll() as $user) {
                $Users[] = new CoinUser(
                    $user['user_id'],
                    $user['user_name'],
                    $user['balance'],
                    $user['last_activity']
                );
            }
            return $Users;
        }
        return false;
    }

    public function GetUsersByBottomBalance($number)
    {
        $sql = 'SELECT user_id, user_name, balance, last_activity
				FROM coin_users
				ORDER BY balance ASC
				LIMIT :number';

        $query = $this->db->prepare($sql);
        $query->bindValue(':number', $number, \PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount()) {
            $Users = array();
            foreach ($query->fetchAll() as $user) {
                $Users[] = new CoinUser(
                    $user['user_id'],
                    $user['user_name'],
                    $user['balance'],
                    $user['last_activity']
                );
            }
            return $Users;
        }
        return false;
    }

    public function GetNumberOfTransactions()
    {
        $sql = 'SELECT id FROM coin_transactions';

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->rowCount();
    }

    public function GetNumberOfTransactionsByUser(CoinUser $User)
    {
        $sql = 'SELECT id FROM coin_transactions WHERE user_sending = :user_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $User->user_id);
        $query->execute();

        return $query->rowCount();
    }

    public function RetrieveRecentLogsByUser(CoinUser $User, $number)
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

    public function RetrieveRecentLogs($number)
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

    public function AddTransactionLog(Transaction $Transaction)
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

    public function CollectPeriodicTax()
    {
        $sql = 'UPDATE coin_users
				SET balance = (balance * ' . (1 - COIN_PERIODIC_TAX) . ')
				WHERE user_name != "' . COIN_TAXATION_BODY . '";';

        $query = $this->db->prepare($sql);
        return $query->execute();
    }
}