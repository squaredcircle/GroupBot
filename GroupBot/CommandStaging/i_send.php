<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:30 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Coin\Money\Transact;
use GroupBot\Brains\Query;
use GroupBot\Telegram;
use GroupBot\Brains\Coin\Enums\TransactionType;
use GroupBot\Brains\Coin\Types\Transaction;
use GroupBot\Types\Command;

class i_send extends Command
{
    public function i_send()
    {
        $Transact = new Transact($this->db);

        if ($this->noParams() == 2)
        {
            $user_receiving = Query::getUserMatchingStringOrErrorMessage($this->db, $this->Message->Chat, $this->getParam());

            if (is_string($user_receiving)) {
                Telegram::talk($this->Message->Chat->id, $user_receiving);
                return false;
            }

            $Transaction = new Transaction(
                $this->Message->User,
                $user_receiving,
                $this->getParam(1),
                new TransactionType(TransactionType::Manual)
            );
            $Transact->performTransaction($Transaction);

            if ($Feedback = $Transact->Feedback->getFeedback()) {
                Telegram::talk($this->Message->Chat->id, emoji("0x1F4E2") . " " . $Feedback);
            } else {
                Telegram::talk($this->Message->Chat->id, "I'm so sorry brah...");
            }
        } else {
            Telegram::talk($this->Message->Chat->id, "Like this fam " . emoji("0x1F449") . "  /send alex 10");
        }
        return true;
    }
}