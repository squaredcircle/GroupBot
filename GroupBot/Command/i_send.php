<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:30 AM
 */
namespace GroupBot\Command;

use GroupBot\Base\Telegram;
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
                Telegram::talk($this->Message->Chat->id, emoji("0x1F44E") . " You don't have an " . COIN_CURRENCY_NAME . " account, brah...");
                return false;
            }
            if (!$user_receiving) {
                Telegram::talk($this->Message->Chat->id,  emoji("0x1F44E") . " Can't find a user called `" . $this->getParam() . "`, brah");
                return false;
            }

            $Transaction = new Transaction(
                NULL,
                $user_sending,
                $user_receiving,
                $this->getParam(1),
                new TransactionType(TransactionType::Manual)
            );
            $Coin->Transact->performTransaction($Transaction);

            if ($Feedback = $Coin->Feedback->getFeedback()) {
                Telegram::talk($this->Message->Chat->id, emoji("0x1F4E2") . " " . $Feedback);
            } else {
                Telegram::talk($this->Message->Chat->id, "I'm so sorry brah...");
            }
        } else {
            Telegram::talk($this->Message->Chat->id, "Like this fam " . emoji("0x1F449") . "  /send alex 10");
        }
    }
}