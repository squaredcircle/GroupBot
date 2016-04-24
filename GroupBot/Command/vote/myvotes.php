<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:30 AM
 */
namespace GroupBot\Command\vote;

use GroupBot\Telegram;
use GroupBot\Brains\Vote\Enums\VoteType;
use GroupBot\Brains\Vote\Vote;
use GroupBot\Types\Command;

class myvotes extends Command
{
    public function main()
    {
        $Vote = new Vote($this->db);
        $user_vote_total = $Vote->getVoteTotalForUserInChat($this->Message->User, $this->Message->Chat->id);
        $user_vote_from = $Vote->getUserVotesInChat($this->Message->User, $this->Message->Chat->id);

        $out = "You're on *$user_vote_total*. ";

        if (count($user_vote_from) > 0) {
            $out .= "You've voted as follows:";
            $thumbs_up = emoji(0x1F44D);
            $thumbs_down = emoji(0x1F44E) . emoji(0x1F3FF);

            foreach ($user_vote_from as $userVote) {
                $emoji = $userVote->vote == VoteType::Up ? $thumbs_up : $thumbs_down;
                $out .= "\n`   `• " . $emoji . " *" . ($userVote->voted_for ? $userVote->voted_for->getName() : 'uhoh') . "* ";
            }
        } else {
            $out .= "You haven't cast any votes.";
        }

        Telegram::talk($this->Message->Chat->id, $out);
    }
}