<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:30 AM
 */
namespace GroupBot\Command\vote;

use GroupBot\Telegram;
use GroupBot\Brains\Vote\Vote;
use GroupBot\Types\Command;

class allvotes extends Command
{
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

    public function main()
    {
        $Vote = new Vote($this->db);
        $leaderboard = $Vote->getVoteLeaderboard($this->Message->Chat->id);

        $out = '';
        $index = 0;

        if (!empty($leaderboard)) {
            foreach ($leaderboard as $uservote)
            {
                $index++;
                $out .= "`" . $this->addOrdinalNumberSuffix($index);
                if ($index >= 10) {
                    $out .= " `";
                } else {
                    $out .= "  `";
                }

                $vote_prefix = $uservote->vote_total > 0 ? "+" : "";
                if (!isset($uservote->vote_total)) $uservote->vote_total = 0;

                $out .= "*" . $uservote->user->getName() . "* (" . $vote_prefix . $uservote->vote_total . ")\n";
            }
        } else {
            $out .= "No users to display.";
        }

        Telegram::talk($this->Message->Chat->id, $out);
    }
}