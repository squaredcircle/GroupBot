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

class v_vote extends Command
{
    public function v_vote()
    {
        $log = new Logging($this->Message);

        if ($this->noParams() == 2) {
            $user_id = $log->checkIfUserIsLogged($this->getParam(0));
            if (strcasecmp($this->getParam(0), BOT_FRIENDLY_NAME) === 0) {
                Telegram::talk($this->Message->Chat->id, "wow, thx brah! " . emoji(0x1F618));
                return false;
            }
            if (!$user_id) {
                Telegram::talk($this->Message->Chat->id, emoji(0x1F44E) . " Can't find that user, brah");
                return false;
            }
            if ($user_id == $this->Message->User->id) {
                Telegram::talk($this->Message->Chat->id, emoji(0x1F44E) . " You can't vote for yourself!");
                return false;
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
                    Telegram::talk($this->Message->Chat->id, emoji(0x1F44E) . " Your vote must be either *up*, *down* or *neutral*.");
                    return false;
            }

            $voted_for = new User();
            $voted_for->id = $user_id;
            $userVote = new UserVote();
            $userVote->construct($this->Message->User, $voted_for, $voteType);

            $Vote = new Vote();
            $Vote->SQL->update_vote($userVote);

            Telegram::talk($this->Message->Chat->id, emoji(0x1F528) . " Vote updated.");
        } else {
            Telegram::talk($this->Message->Chat->id, emoji(0x1F44E) . " Like this fam " . emoji("0x1F449") . "  /vote richard up\nYou can use /myvotes or /allvotes to see more.");
        }
    }
}