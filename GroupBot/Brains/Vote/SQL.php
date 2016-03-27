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

class SQL
{
    /**
     * @var \PDO
     */
    protected $db;

    public function __construct()
    {
        $dbcontrol = new DbControl();
        $this->db = $dbcontrol->getObject();
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
            return $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Brains\Vote\Types\UserVote');
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
            return $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Brains\Vote\Types\UserVote');
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
            return $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Brains\Vote\Types\UserVote');
        }
        return false;
    }

    /**
     * @param UserVote $userVote
     * @return bool
     */
    public function update_vote(UserVote $userVote)
    {
        if ($userVote->vote == VoteType::Neutral)
        {
            $sql = 'DELETE FROM user_votes WHERE voter = :voter';

            $query = $this->db->prepare($sql);
            $query->bindValue(':voter', $userVote->voter->id);

            return $query->execute();
        }
        else
        {
            $sql = 'INSERT INTO user_votes (voter, voted_for, vote)
                    VALUES (:voter, :voted_for, :vote) ON DUPLICATE KEY
                    UPDATE voted_for = :voted_for, vote = :vote';

            $query = $this->db->prepare($sql);
            $query->bindValue(':voter', $userVote->voter->id);
            $query->bindValue(':voted_for', $userVote->voted_for->id);
            $query->bindValue(':vote', $userVote->vote);

            return $query->execute();
        }
    }
}