<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:30 AM
 */
namespace GroupBot\Command;

use GroupBot\Base\Logging;
use GroupBot\Telegram;
use GroupBot\Brains\Vote\Enums\VoteType;
use GroupBot\Brains\Vote\Types\UserVote;
use GroupBot\Brains\Vote\Vote;
use GroupBot\Types\Command;
use GroupBot\Types\User;

class v_myvotes extends Command
{
    public function v_myvotes()
    {
        $Vote = new Vote();
        $user_vote_total = $Vote->getVoteTotalForUser($this->Message->User);
        $user_vote_from = $Vote->SQL->get_votes_from_user($this->Message->User->id);

        $out = "You're on *$user_vote_total*. You've voted as follows:";

        foreach ($user_vote_from as $userVote) {
            $out .= "\n`   `• *" . $userVote->voted_for->first_name . "* ";
            $out .= $userVote->vote == VoteType::Up ? emoji(0x1F44D) : emoji(0x1F44E);
        }

        Telegram::talk($this->Message->Chat->id, $out);
    }
}