<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:30 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Coin\Coin;
use GroupBot\Brains\Coin\Enums\TransactionType;
use GroupBot\Brains\Coin\Types\Transaction;
use GroupBot\Types\Command;

class i_send extends Command
{
    public function i_send()
    {
        $Coin = new Coin();

        if ($this->noParams() == 2)
        {
            $user_sending = $Coin->SQL->GetUserById($this->Message->User->id);
            $user_receiving = $Coin->SQL->GetUserByName($this->getParam());

            if (!$user_sending) {
                $this->Telegram->talk($this->Message->Chat->id, emoji("0x1F44E") . " You don't have an " . COIN_CURRENCY_NAME . " account, brah...");
                return false;
            }
            if (!$user_receiving) {
                $this->Telegram->talk($this->Message->Chat->id,  emoji("0x1F44E") . " Can't find a user called `" . $this->getParam() . "`, brah");
                return false;
            }

            $Transaction = new Transaction(
                NULL,
                $Coin->SQL->GetUserById($this->Message->User->id),
                $Coin->SQL->GetUserByName($this->getParam()),
                $this->getParam(1),
                new TransactionType(TransactionType::Manual)
            );
            if ($Coin->Transact->performTransaction($Transaction))
            {
                if ($Feedback = $Coin->Feedback->getFeedback()) {
                    $this->Telegram->talk($this->Message->Chat->id, emoji("0x1F4E2") . " " . $Feedback);
                } else {
                    $this->Telegram->talk($this->Message->Chat->id, "You sent " . $this->getParam() . " " . $this->getParam(1) . ", gj brah");
                }
            } else {
                $this->Telegram->talk($this->Message->Chat->id, emoji("0x1F440") . " You gotta /link your account first brah...");
            }
        } else {
            $this->Telegram->talk($this->Message->Chat->id, "Like this fam " . emoji("0x1F449") . "  /send alex 10");
        }
    }
}