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

    /** @var  \PDO */
    public $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
        $this->SQL = new SQL($db);
    }

    private function in_array_field($needle, $needle_field, $haystack, $strict = false)
    {
        if ($strict) {
            foreach ($haystack as $item)
                if (isset($item->$needle_field) && $item->$needle_field === $needle)
                    return true;
        } else {
            foreach ($haystack as $item)
                if (isset($item->$needle_field) && $item->$needle_field == $needle)
                    return true;
        }
        return false;
    }

    /**
     * @param User $user
     * @return int
     */
    public function getVoteTotalForUser(User $user)
    {
        if ($votes = $this->SQL->get_votes_on_user($user->user_id)) {
            $total = 0;
            foreach ($votes as $vote) {
                $total += $vote->vote;
            }
            return $total;
        }
        return 0;
    }

    /**
     * @param User $user
     * @return int
     */
    public function getVoteTotalForUserInChat(User $user, $chat_id)
    {
        $DbUser = new \GroupBot\Database\User($this->db);
        $users = $DbUser->getAllUsersInChat($chat_id);

        if ($votes = $this->SQL->get_votes_on_user($user->user_id)) {
            $total = 0;
            foreach ($votes as $vote) {
                if ($this->in_array_field($vote->voter->user_id, 'user_id', $users)) {
                    $total += $vote->vote;
                }
            }
            return $total;
        }
        return 0;
    }

    public function getUserVotesInChat(User $user, $chat_id)
    {
        $DbUser = new \GroupBot\Database\User($this->db);
        $users = $DbUser->getAllUsersInChat($chat_id);
        $user_vote_from = $this->SQL->get_votes_from_user($user->user_id);

        $out = [];
        foreach ($user_vote_from as $uservote) {
            if ($this->in_array_field($uservote->voted_for->user_id, 'user_id', $users)) {
                $out[] = $uservote;
            }
        }
        return $out;
    }

    /**
     * @param $chat_id
     * @return LeaderboardItem[]
     */
    public function getVoteLeaderboard($chat_id)
    {
        $DbUser = new \GroupBot\Database\User($this->db);
        if (isset($chat_id)) {
            $users = $DbUser->getAllUsersInChat($chat_id);
        } else {
            $users = $DbUser->getAllUsers(true);
        }
        $votes = $this->SQL->get_all_votes();

        $tallies = array();
        if ($votes) {
            foreach ($votes as $vote) {
                if ($this->in_array_field($vote->voter->user_id, 'user_id', $users)) {
                    if (isset($tallies[$vote->voted_for->user_id])) {
                        $tallies[$vote->voted_for->user_id] += $vote->vote;
                    } else {
                        $tallies[$vote->voted_for->user_id] = $vote->vote;
                    }
                }
            }
        }

        $leaderboard = array();
        foreach ($users as $user) {
            $tally = array_key_exists($user->user_id, $tallies) ? $tallies[$user->user_id] : 0;
            $leaderboard[] = new LeaderboardItem($user, $tally);
        }

        usort($leaderboard, function ($a, $b) {
            if ($a->vote_total == $b->vote_total)
                return 0;
            return $a->vote_total > $b->vote_total ? -1 : 1;
        });

        return $leaderboard;
    }
}
