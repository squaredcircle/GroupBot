<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 20/02/2016
 * Time: 12:52 PM
 */

namespace GroupBot\Brains\Todo;


use GroupBot\Brains\Todo\Enums\VoteType;
use GroupBot\Brains\Todo\Types\TodoItem;
use GroupBot\Brains\Todo\Types\UserVote;
use GroupBot\Database\DbConnection;
use GroupBot\Database\User;

class SQL extends DbConnection
{
    public function check_if_todo_exists($todo_id)
    {
        $sql = 'SELECT id FROM todo WHERE id = :id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':id', $todo_id);
        $query->execute();

        return (bool)$query->rowCount();
    }

    /**
     * @param int $todo_id
     * @return bool|TodoItem
     */
    public function get_todo($todo_id)
    {
        $sql = 'SELECT * FROM todo WHERE id = :id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':id', $todo_id);
        $query->execute();

        if ($query->rowCount()) {
            $query->setFetchMode(\PDO::FETCH_CLASS, 'GroupBot\Brains\Todo\Types\TodoItem');
            return $query->fetch();
        }
        return false;
    }

    /**
     * @return TodoItem[]|bool
     */
    public function get_all_todos()
    {
        $sql = 'SELECT * FROM todo';
        $query = $this->db->prepare($sql);
        $query->execute();

        if ($query->rowCount()) {
            if ($items = $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Brains\Todo\Types\TodoItem')) {
                return $items;
            }
        }
        return false;
    }

    public function add_todo(TodoItem $item)
    {
        $sql = 'INSERT INTO todo (user_id, description)
                VALUES (:user_id, :description)';

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $item->owner->user_id);
        $query->bindValue(':todo_id', $item->id);
        $query->execute();
        return $this->db->lastInsertId();
    }

    /**
     * @param UserVote[] $votes
     * @return UserVote[]
     */
    private function map_users($votes)
    {
        $DbUser = new User($this->db);
        foreach ($votes as $vote) {
            $vote->voter = $DbUser->getUserFromId($vote->voter);
        }
        return $votes;
    }

    /**
     * @param UserVote $userVote
     * @return bool
     */
    public function check_if_vote_exists(UserVote $userVote)
    {
        $sql = 'SELECT vote FROM todo_votes WHERE user_id = :user_id AND todo_id = :todo_id AND vote = :vote';

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $userVote->voter->user_id);
        $query->bindValue(':todo_id', $userVote->todo->id);
        $query->bindValue(':vote', $userVote->vote);
        $query->execute();

        return (bool)$query->rowCount();
    }

    /**
     * @return UserVote[]|bool
     */
    public function get_all_votes()
    {
        $sql = 'SELECT user_id, todo_id, vote FROM todo_votes';
        $query = $this->db->prepare($sql);
        $query->execute();

        if ($query->rowCount()) {
            if ($votes = $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Brains\Todo\Types\UserVote')) {
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
        $sql = 'SELECT user_id, todo_id, vote FROM todo_votes WHERE user_id= :user_id';
        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $user_id);
        $query->execute();

        if ($query->rowCount()) {
            if ($votes = $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Brains\Todo\Types\UserVote')) {
                return $this->map_users($votes);
            }
        }
        return false;
    }

    /**
     * @param $todo_id
     * @return UserVote[]|bool
     */
    public function get_votes_on_todo($todo_id)
    {
        $sql = 'SELECT user_id, todo_id, vote FROM todo_votes WHERE todo_id= :todo_id';
        $query = $this->db->prepare($sql);
        $query->bindValue(':todo_id', $todo_id);
        $query->execute();

        if ($query->rowCount()) {
            if ($votes = $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Brains\Todo\Types\UserVote')) {
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
            $sql = 'DELETE FROM todo_votes WHERE user_id = :user_id AND todo_id = :todo_id';

            $query = $this->db->prepare($sql);
            $query->bindValue(':user_id', $userVote->voter->user_id);
            $query->bindValue(':todo_id', $userVote->todo->id);

            return $query->execute();
        } else {
            $sql = 'INSERT INTO todo_votes (user_id, todo_id, vote)
                    VALUES (:user_id, :todo_id, :vote) ON DUPLICATE KEY
                    UPDATE vote = :vote2';

            $query = $this->db->prepare($sql);
            $query->bindValue(':user_id', $userVote->voter->user_id);
            $query->bindValue(':todo_id', $userVote->todo->id);
            $query->bindValue(':vote', $userVote->vote);
            $query->bindValue(':vote2', $userVote->vote);
            
            return $query->execute();
        }
    }
}