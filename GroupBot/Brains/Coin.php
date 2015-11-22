<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 13/11/2015
 * Time: 9:34 AM
 */

namespace GroupBot\Brains;


use Coin\Auth\TelegramLink;

class Coin
{
    private $ic;

    public function __construct()
    {
        require(COIN_CLASS);
        $this->ic =  new \Coin\Base\Coin(true);
    }

    public function getObject()
    {
        return $this->ic;
    }

    public function createPendingLink($user_id, $key)
    {
        $this->ic->TelegramLink->createPendingLink($user_id, $key);
    }

    public function checkIfUserLinked($user_id)
    {
        return $this->ic->TelegramLink->checkIfUserIsLinkedByTelegramId($user_id);
    }

    private function checkIfUserExists($user_name)
    {
        return $this->ic->UserControl->doesUserExist($user_name);
    }

    public function performTransaction($user_id, $user_receiving, $amount, $Telegram)
    {
        if ($this->checkIfUserLinked($user_id)) {
            $user_sending = $this->ic->TelegramLink->getUserFromTelegramId($user_id);
            $this->ic->MoneyControl->performTransaction($user_sending, $user_receiving, $amount, $Telegram);
            return true;
        }
        return false;
    }

    public function getFeedback()
    {
        return $this->ic->Feedback->isFeedback() ? $this->ic->Feedback->getFeedback() : false;
    }

    public function getBalanceByUserName($user_name)
    {
        if ($this->checkIfUserExists($user_name)) {
            return round($this->ic->Check->getBalance($user_name),2);
        }
        return -1;
    }

    public function getBalanceByUserId($user_id)
    {
        if ($this->checkIfUserLinked($user_id)) {
            $user_name = $this->ic->TelegramLink->getUserFromTelegramId($user_id);
            return round($this->ic->Check->getBalance($user_name), 2);
        }
        return -1;
    }

}