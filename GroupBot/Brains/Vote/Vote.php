<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 20/02/2016
 * Time: 12:52 PM
 */

namespace GroupBot\Brains\Vote;


use GroupBot\Base\DbControl;
use GroupBot\Brains\Vote\Types\LeaderboardItem;
use GroupBot\Types\User;

class Vote
{
    /** @var  SQL */
    public $SQL;

    public function __construct()
    {
        $this->SQL = new SQL();
    }

    /**
     * @param User $user
     * @return int
     */
    public function getVoteTotalForUser(User $user)
    {
        if ($votes = $this->SQL->get_votes_on_user($user->id)) {
            $total = 0;
            foreach ($votes as $vote) {
                $total += $vote->vote;
            }
            return $total;
        }
        return 0;
    }

    /**
     * @param $chat_id
     * @return LeaderboardItem[]
     */
    public function getVoteLeaderboard($chat_id)
    {
        $dbcontrol = new DbControl();
        $users = $dbcontrol->getAllUsersInChat($chat_id);
        $votes = $this->SQL->get_all_votes();

        $tallies = array();
        if ($votes) {
            foreach ($votes as $vote) $tallies[$vote->voted_for->id] += $vote->vote;
        }

        $leaderboard = array();
        foreach ($users as $user) {
            $tally = array_key_exists($user->id, $tallies) ? $tallies[$user->id] : 0;
            $leaderboard[] = new LeaderboardItem($user, $tally);
        }

        usort($leaderboard, function($a, $b) {
            if ($a->vote_total == $b->vote_total) return 0;
            return $a->vote_total > $b->vote_total ? -1 : 1;
        });

        return $leaderboard;
    }
}