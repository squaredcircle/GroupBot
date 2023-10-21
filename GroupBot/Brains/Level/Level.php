<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 17/02/2016
 * Time: 11:26 PM
 */

namespace GroupBot\Brains\Level;


use GroupBot\Brains\Coin\Enums\TransactionType;
use GroupBot\Brains\Coin\Money\Transact;
use GroupBot\Brains\Coin\Types\BankTransaction;
use GroupBot\Types\User;

class Level
{
    public static function getLevelPrice($level)
    {
        return $level * 10;
    }

    public static function cumulativeLevelPrice($level)
    {
        return (5 * $level * ($level + 1) - 10);
    }

    public static function getDailyAllowance($level)
    {
        return COIN_DAILY_INCOME + $level;
    }

    public static function buyLevel(User $user, \PDO $db)
    {
        $price = Level::getLevelPrice($user->level+1);

        if ($user->getBalance() >= $price) {
            $Transact = new Transact($db);
            if ($Transact->transactToBank(new BankTransaction(
                $user,
                $price,
                TransactionType::LevelPurchase
            ))) {
                $user->level++;
                $user->save($db);
                return true;
            }
        }
        return false;
    }
}