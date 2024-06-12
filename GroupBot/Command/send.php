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

            if ($this->Message->User->level < 5) {
                Telegram::talk($this->Message->Chat->id, "❌ You need to be Level 5 to use /send.");
                return false;
            }

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

//            if ($Transact->Validate->checkAmount($this->getParam(1)))
//            {
//                $transferred_today = $Transact->CoinSQL->retrieveCoinTransferredToday($this->Message->User);
//                if (!is_bool($transferred_today) && $transferred_today + $this->getParam(1) > $this->Message->User->level)
//                {
//                    Telegram::talk($this->Message->Chat->id, "❌ As you're *Level " . $this->Message->User->level . "*, you can only send a maximum of `" . $this->Message->User->level . "` coin per day. Rise in level to increase this limit.");
//                    return false;
//                }
//            }

            $Transaction = new Transaction(
                $this->Message->User,
                $user_receiving,
                $this->getParam(1),
                TransactionType::Manual
            );

            $Transact->performTransaction($Transaction);

            if (strcmp($Transact->Feedback->getFeedbackCodes(), '24') === 0) {
                $out = emoji("0x1F4E2") . " " . $Transact->Feedback->getFeedback()
                    . "\n`   `• `" . $user_receiving->getName() . "` now has 💰*" . $user_receiving->getBalance() . "*"
                    . "\n`   `• `You've` got 💰*" . $this->Message->User->getBalance() . "* left.";

                if ($user_receiving->user_id != -1) $out .=
                    "\n`   `• `The Bank` took 2%, or 💰*" . round($this->getParam(1) * 0.02,2) . "*";

                Telegram::talk($this->Message->Chat->id, $out);
            } else {
                Telegram::talk($this->Message->Chat->id, emoji("0x1F4E2") . " " . $Transact->Feedback->getFeedback());
            }
        } else {
            Telegram::talk($this->Message->Chat->id, "Like this fam " . emoji("0x1F449") . "  /send richardstallman 10");
        }
        return true;
    }
}