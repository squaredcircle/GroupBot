<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 25/04/2016
 * Time: 2:59 PM
 */

namespace GroupBot\Brains\Minesweeper;


use GroupBot\Brains\Minesweeper\Types\Game;
use GroupBot\Database\DbConnection;

class SQL extends DbConnection
{
    public function insert_game(Game $game)
    {
        $sql = 'INSERT INTO ms_games (chat_id, board) VALUES (:chat_id, :board)';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $game->chat_id);
        $query->bindValue(':board', $game->board->toDbString());

        return $query->execute();
    }

    public function update_game(Game $game)
    {
        $sql = 'UPDATE ms_games 
                SET board = :board
                WHERE chat_id = :chat_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $game->chat_id);
        $query->bindValue(':board', $game->board->toDbString());

        return $query->execute();
    }
}