<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 20/02/2016
 * Time: 12:52 PM
 */

namespace GroupBot\Brains\Vote;


use GroupBot\Base\DbControl;
use GroupBot\Brains\Vote\Enums\VoteType;
use GroupBot\Brains\Vote\Types\UserVote;
use GroupBot\Database\DbConnection;
use GroupBot\Database\User;
use GroupBot\Telegram;

class SQL extends DbConnection
{
    /**
     * @param UserVote[] $votes
     * @return UserVote[]
     */
    private function map_users($votes)
    {
        $DbUser = new User($this->db);
        foreach ($votes as $vote) {
            $vote->voter = $DbUser->getUserFromId($vote->voter);
            $vote->voted_for = $DbUser->getUserFromId($vote->voted_for);
        }
        return $votes;
    }

    /**
     * @param UserVote $userVote
     * @return bool
     */
    public function check_if_vote_exists(UserVote $userVote)
    {
        $sql = 'SELECT vote FROM user_votes WHERE voter = :voter AND voted_for = :voted_for AND vote = :vote';


        $query = $this->db->prepare($sql);
        $query->bindValue(':voter', $userVote->voter->user_id);
        $query->bindValue(':voted_for', $userVote->voted_for->user_id);
        $query->bindValue(':vote', $userVote->vote);
        $query->execute();

        return (bool)$query->rowCount();
    }

    /**
     * @return UserVote[]|bool
     */
    public function get_all_votes()
    {
        $sql = 'SELECT voter, voted_for, vote FROM user_votes';
        $query = $this->db->prepare($sql);
        $query->execute();

        if ($query->rowCount()) {
            if ($votes = $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Brains\Vote\Types\UserVote')) {
                return $this->map_users($votes);
            }
        }
        return false;
    }

    /**
     * @param $user_id
     * @return UserVote[]|bool
     */
    public function get_votes_from_user($user_id)
    {
        $sql = 'SELECT voter, voted_for, vote FROM user_votes WHERE voter = :voter';
        $query = $this->db->prepare($sql);
        $query->bindValue(':voter', $user_id);
        $query->execute();

        if ($query->rowCount()) {
            if ($votes = $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Brains\Vote\Types\UserVote')) {
                return $this->map_users($votes);
            }
        }
        return false;
    }

    /**
     * @param $user_id
     * @return UserVote[]|bool
     */
    public function get_votes_on_user($user_id)
    {
        $sql = 'SELECT voter, voted_for, vote FROM user_votes WHERE voted_for = :voted_for';
        $query = $this->db->prepare($sql);
        $query->bindValue(':voted_for', $user_id);
        $query->execute();

        if ($query->rowCount()) {
            if ($votes = $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Brains\Vote\Types\UserVote')) {
                return $this->map_users($votes);
            }
        }
        return false;
    }

    /**
     * @param UserVote $userVote
     * @return bool
     */
    public function update_vote(UserVote $userVote)
    {
        if ($userVote->vote == VoteType::Neutral) {
            $sql = 'DELETE FROM user_votes WHERE voter = :voter AND voted_for = :voted_for';

            $query = $this->db->prepare($sql);
            $query->bindValue(':voter', $userVote->voter->user_id);
            $query->bindValue(':voted_for', $userVote->voted_for->user_id);

            return $query->execute();
        } else {
            $sql = 'INSERT INTO user_votes (voter, voted_for, vote)
                    VALUES (:voter, :voted_for, :vote) ON DUPLICATE KEY
                    UPDATE voted_for = :voted_for2, vote = :vote2';

            $query = $this->db->prepare($sql);
            $query->bindValue(':voter', $userVote->voter->user_id);
            $query->bindValue(':voted_for', $userVote->voted_for->user_id);
            $query->bindValue(':vote', $userVote->vote);
            $query->bindValue(':voted_for2', $userVote->voted_for->user_id);
            $query->bindValue(':vote2', $userVote->vote);
            
            return $query->execute();
        }
    }
}