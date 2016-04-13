<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:30 AM
 */
namespace GroupBot\Command\vote;

use GroupBot\Brains\Query;
use GroupBot\Telegram;
use GroupBot\Brains\Vote\Enums\VoteType;
use GroupBot\Brains\Vote\Types\UserVote;
use GroupBot\Types\Command;
use GroupBot\Types\User;

class vote extends Command
{
    private function addOrdinalNumberSuffix($num)
    {
        if (!in_array(($num % 100), array(11, 12, 13))) {
            switch ($num % 10) {
                // Handle 1st, 2nd, 3rd
                case 1:
                    return $num . 'st';
                case 2:
                    return $num . 'nd';
                case 3:
                    return $num . 'rd';
            }
        }
        return $num . 'th';
    }

    private function leaderboard()
    {
        $Vote = new \GroupBot\Brains\Vote\Vote($this->db);
        $leaderboard = $Vote->getVoteLeaderboard($this->Message->Chat->id);

        $out = '';
        $index = 0;

        if (!empty($leaderboard)) {
            foreach ($leaderboard as $uservote) {
                $index++;
                $out .= "`" . $this->addOrdinalNumberSuffix($index);
                if ($index >= 10) {
                    $out .= " `";
                } else {
                    $out .= "  `";
                }

                $vote_prefix = $uservote->vote_total > 0 ? "+" : "";
                if (!isset($uservote->vote_total))
                    $uservote->vote_total = 0;

                $out .= "*" . $uservote->user->getName() . "* (" . $vote_prefix . $uservote->vote_total . ")\n";
            }
        } else {
            $out .= "No users to display.";
        }

        return $out;
    }

    private function performVote()
    {
        $DbUser = new \GroupBot\Database\User($this->db);
        
        $user = Query::getUserMatchingStringOrErrorMessage($this->db, $this->Message->Chat, $this->getParam(0));
        if (is_string($user)) return $user;

        if (strcasecmp($this->getParam(0), BOT_FRIENDLY_NAME) === 0) {
            return "wow, thx brah! " . emoji(0x1F618);
        }
        if (!$user) {
            return emoji(0x1F44E) . " Can't find that user, brah";
        }
        if ($user->user_id == $this->Message->User->user_id) {
            return emoji(0x1F44E) . " You can't vote for yourself!";
        }
        switch ($this->getParam(1)) {
            case 'up':
                $voteType = new VoteType(VoteType::Up);
                break;
            case 'down':
                $voteType = new VoteType(VoteType::Down);
                break;
            case 'neutral':
                $voteType = new VoteType(VoteType::Neutral);
                break;
            default:
                return emoji(0x1F44E) . " Your vote must be either *up*, *down* or *neutral*.";
        }

        $voted_for = new User();
        $voted_for->user_id = $user->user_id;
        $userVote = new UserVote();
        $userVote->construct($this->Message->User, $voted_for, $voteType);

        $Vote = new \GroupBot\Brains\Vote\Vote($this->db);
        $Vote->SQL->update_vote($userVote);

        return emoji(0x1F528) . " Vote updated.";
    }

    public function main()
    {
        if ($this->noParams() == 2) {
            $out = $this->performVote();
            $out .= "\nThe leaderboard for *" . $this->Message->Chat->title . "* is now:\n\n";
            $out .= $this->leaderboard();
            $out .= "\nYou can see your votes with /myvotes.";
        } else {
            $out = "Voting leaderboard for *" . $this->Message->Chat->title . "*:\n\n";
            $out .= $this->leaderboard();
            $out .= "\nYou can vote for others like this " . emoji("0x1F449") . "  `/vote richardstallman up`\nYou can see your votes with /myvotes.";
        }

        Telegram::talk($this->Message->Chat->id, $out);
    }
}