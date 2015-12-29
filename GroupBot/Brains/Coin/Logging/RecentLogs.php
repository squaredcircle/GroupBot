<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 26/10/2015
 * Time: 12:33 AM
 */

namespace GroupBot\Brains\Coin\Logging;


use GroupBot\Brains\Coin\Enums\TransactionType;
use GroupBot\Brains\Coin\SQL;
use GroupBot\Brains\Coin\Types\CoinUser;

class RecentLogs
{
    private $SQL;

    public function __construct(SQL $SQL)
    {
        $this->SQL = $SQL;
    }

    public function getRecentLogsText()
    {
        $logs = $this->SQL->RetrieveRecentLogs('5');

        $out = "";

        if (!empty($logs)) {
            $logs = array_slice($logs, 0, 4);
            foreach ($logs as $i) {

                $date = date('D jS g:iA', strtotime($i['date']));

                $out .= "\n`" . $date . "` \n` `" . emoji(0x27A1) . " *";

                if ($i->type == TransactionType::Manual)
                    $out .= $i->user_sending . "* ";
                elseif ($i->type == TransactionType::TransactionTax || $i->type == TransactionType::AllTax)
                    $out .= COIN_TAXATION_BODY . "* collected ";
                elseif ($i->type == TransactionType::RedistributionTax)
                    $out .= COIN_REDISTRIBUTION_BODY . "* redistributed ";

                if ($i->type == TransactionType::Manual)
                    $out .= " (" . round($i->amount, 2) . " " . $i->user_receiving->user_name ." tax)";
                elseif ($i->type == TransactionType::TransactionTax || $i->type == TransactionType::AllTax)
                    $out .= round($i->amount, 2) . " in tax";
                elseif ($i->type == TransactionType::RedistributionTax)
                    $out .= round($i->amount, 2) . " of *" . COIN_TAXATION_BODY . "'s* wealth";
                elseif (strcmp($i->user_receiving->user_name, COIN_TAXATION_BODY))
                    $out .= 'sent ' . round($i->amount, 2) . " to *" . $i->user_receiving->user_name ."*";
                else
                    $out .= 'donated ' . round($i->amount, 2) . " to *" . $i->user_receiving->user_name ."*";

            }
        } else {
            $out .= "No recent transactions found.";
        }

        return $out;
    }

    public function getUserHistory(CoinUser $User)
    {
        $logs = $this->SQL->RetrieveRecentLogsByUser($User, '5');

        if (!empty($logs)) {
            foreach ($logs as $i)
                if ($i->user_sending->user_id == $User->user_id) {
                    echo "\n`[" . $i->date . "]`";
                    if ($i->type == TransactionType::TransactionTax || $i->type == TransactionType::AllTax) {
                        echo "You were taxed ";
                    } elseif (!strcmp($i->user_receiving->user_id, COIN_TAXATION_BODY))
                    {
                        echo "You donated ";
                    } else {
                        echo "You sent ";
                    }
                   echo "*" . round($i->amount, 2) . "* to *" . $i->user_receiving->user_name . "*\n";

                } else {
                    echo "`[" . $i->date . "]` *" . $i->user_sending->user_name . "* sent you *" . round($i->amount, 2) . "*\n";
                }
        } else {
            echo "No transaction history to display.";
        }
    }
}