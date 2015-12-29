<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 26/10/2015
 * Time: 12:28 AM
 */

namespace GroupBot\Brains\Coin\Logging;


use GroupBot\Brains\Coin\SQL;

class Leaderboard
{
    private $SQL;

    public function __construct(SQL $SQL)
    {
        $this->SQL = $SQL;
    }

    private function addOrdinalNumberSuffix($num) {
        if (!in_array(($num % 100),array(11,12,13))){
            switch ($num % 10) {
                // Handle 1st, 2nd, 3rd
                case 1:  return $num.'st';
                case 2:  return $num.'nd';
                case 3:  return $num.'rd';
            }
        }
        return $num.'th';
    }

    public function getTextLeaderboard()
    {
        $leaderboard = $this->SQL->GetUsersByTopBalance(10);

        $out = "";

        if (!empty($leaderboard)) {
            foreach ($leaderboard as $key => $i)
            {
                $out .= "`" . $this->addOrdinalNumberSuffix(round(intval($key) + 1.0, 0));
                if (round(intval($key) + 1.0, 0) == 10) {
                    $out .= "  `*";
                } else {
                    $out .= "   `*";
                }
                $out .= $i->user_name . "* (" . round($i->balance, 2) . ")\n";
            }
        } else {
            $out .= "No users to display.";
        }

        return $out;
    }
}