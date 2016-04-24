<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 24/04/2016
 * Time: 12:21 PM
 */

namespace GroupBot\Brains\RussianRoulette;


use GroupBot\Database\DbConnection;

class SQL extends DbConnection
{
    /**
     * @param Game $game
     * @return bool
     */
    public function insert_game(Game $game)
    {
        $sql = 'INSERT INTO rr_games (chat_id, bullet_chamber) VALUES (:chat_id, :bullet_chamber)';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $game->chat_id);
        $query->bindValue(':bullet_chamber', $game->bullet_chamber);

        return $query->execute();
    }

    /**
     * @param $chat_id
     * @return bool|Game
     */
    public function select_game($chat_id)
    {
        $sql = 'SELECT chat_id, current_chamber, bullet_chamber FROM rr_games WHERE chat_id = :chat_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $chat_id);

        $query->execute();

        if ($query->rowCount()) {
            $query->setFetchMode(\PDO::FETCH_CLASS, 'GroupBot\Brains\RussianRoulette\Game');
            return $query->fetch();
        }
        return false;
    }

    /**
     * @param Game $game
     * @return bool
     */
    public function update_game(Game $game)
    {
        $sql = 'UPDATE rr_games SET current_chamber= :current_chamber WHERE chat_id= :chat_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':current_chamber', $game->current_chamber);
        $query->bindValue(':chat_id', $game->chat_id);

        return $query->execute();
    }

    /**
     * @param $chat_id
     * @return bool
     */
    public function delete_game($chat_id)
    {
        $sql = 'DELETE FROM rr_games WHERE chat_id = :chat_id';

        $query = $this->db->prepare($sql);
        $query->bindParam(':chat_id', $chat_id, \PDO::PARAM_INT);

        return $query->execute();
    }

    public function update_stats($user_id, $death = false)
    {
        $sql = 'INSERT INTO rr_stats
                  (user_id, deaths, triggers_pulled)
                VALUES
                  (:user_id, :deaths, 1)
                ON DUPLICATE KEY UPDATE
                  deaths = deaths + :deaths2,
                  triggers_pulled = triggers_pulled + 1';

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $user_id);
        $query->bindValue(':deaths', $death ? 1 : 0, \PDO::PARAM_INT);
        $query->bindValue(':deaths2', $death ? 1 : 0, \PDO::PARAM_INT);

        return $query->execute();
    }
}