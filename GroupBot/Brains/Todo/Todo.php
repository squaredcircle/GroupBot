<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 20/02/2016
 * Time: 12:52 PM
 */

namespace GroupBot\Brains\Todo;


use GroupBot\Brains\Todo\Types\TodoItem;
use GroupBot\Brains\Todo\Types\LeaderboardItem;
use GroupBot\Brains\Todo\Types\VoteTally;

class Todo
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

    /**
     * @param int $todo_id
     * @return int
     */
    public function getVoteTotalForTodo($todo_id)
    {
        if ($votes = $this->SQL->get_votes_on_todo($todo_id)) {
            $total = 0;
            foreach ($votes as $vote) {
                $total += $vote->vote;
            }
            return $total;
        }
        return 0;
    }

    /**
     * @param int $todo_id
     * @return VoteTally
     */
    public function getVoteTalliesForTodo($todo_id)
    {
        if ($votes = $this->SQL->get_votes_on_todo($todo_id)) {
            $voteTally = new VoteTally();
            foreach ($votes as $vote) {
                if ($vote > 0) $voteTally->up++;
                if ($vote == 0) $voteTally->neutral++;
                if ($vote < 0) $voteTally->down--;
                $voteTally->total += $vote->vote;
            }
            return $voteTally;
        }
        return new VoteTally();
    }

    /**
     * @return LeaderboardItem[]
     */
    public function getVoteLeaderboard()
    {
        $votes = $this->SQL->get_all_votes();
        $tallies = array();

        if ($votes) {
            foreach ($votes as $vote) {
                if (isset($tallies[$vote->todo->id])) {
                    $tallies[$vote->todo->id] += $vote->vote;
                } else {
                    $tallies[$vote->todo->id] = $vote->vote;
                }
            }
        }

        $leaderboard = array();
        foreach ($votes as $vote) {
            $tally = array_key_exists($vote->todo->id, $tallies) ? $tallies[$vote->todo->id] : 0;
            $leaderboard[] = new LeaderboardItem($vote->todo, $tally);
        }

        usort($leaderboard, function ($a, $b) {
            if ($a->vote_total == $b->vote_total)
                return 0;
            return $a->vote_total > $b->vote_total ? -1 : 1;
        });

        return $leaderboard;
    }
}
