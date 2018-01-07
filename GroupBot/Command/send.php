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

class send extends Command
{
    public function main()
    {
        $Transact = new Transact($this->db);

        if ($this->noParams() == 2)
        {
            $user_receiving = Query::getUserMatchingStringOrErrorMessage($this->db, $this->Message->Chat, $this->getParam());

            if (strcmp($this->getParam(1), 'love') === 0) {
                Telegram::talk($this->Message->Chat->id, "❤ love is all you need, fam");
                return false;
            }

            if (strcmp($this->getParam(1), 'ree') === 0) {
                Telegram::talk($this->Message->Chat->id, "REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE");
                return false;
            }

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
                $out = emoji("0x1F4E2") . " " . $Feedback
                    . "\n`   `• `" . $user_receiving->getName() . "` now has 💰*" . $user_receiving->getBalance() . "*"
                    . "\n`   `• `You've` got 💰*" . $this->Message->User->getBalance() . "* left.";

                if ($user_receiving->user_id != -1) $out .=
                    "\n`   `• `The Bank` took 2%, or 💰*" . round($this->getParam(1) * 0.02,2) . "*";

                Telegram::talk($this->Message->Chat->id, $out);
            } else {
                Telegram::talk($this->Message->Chat->id, "I'm so sorry brah...");
            }
        } else {
            Telegram::talk($this->Message->Chat->id, "Like this fam " . emoji("0x1F449") . "  /send richardstallman 10");
        }
        return true;
    }
}