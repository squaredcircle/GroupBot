<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 25/04/2016
 * Time: 2:59 PM
 */

namespace GroupBot\Brains\Minesweeper;


use GroupBot\Brains\Minesweeper\Types\Board;
use GroupBot\Brains\Minesweeper\Types\Game;
use GroupBot\Database\DbConnection;

class SQL extends DbConnection
{
    public function insert_game(Game $game)
    {
        $sql = 'INSERT INTO ms_games (chat_id, board, width, height) VALUES (:chat_id, :board, :width, :height)';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $game->chat_id);
        $query->bindValue(':width', $game->board->width);
        $query->bindValue(':height', $game->board->height);
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

    public function delete_game(Game $game)
    {
        $sql = 'DELETE FROM ms_games WHERE chat_id = :chat_id';

        $query = $this->db->prepare($sql);
        $query->bindParam(':chat_id', $game->chat_id);

        return $query->execute();
    }

    public function select_game($chat_id)
    {
        $sql = 'SELECT * FROM ms_games WHERE chat_id = :chat_id';
        
        $query = $this->db->prepare($sql);
        $query->bindParam(':chat_id', $chat_id);

        $query->execute();

        if ($query->rowCount()) {
            $result = $query->fetch();
            $board = new Board();
            $board->fromDbString($result['board'], $result['width'], $result['height']);
            return new Game($board, $chat_id, $result['id']);
        }
        return false;
    }
}
