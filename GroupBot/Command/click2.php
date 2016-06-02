<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 11:17 PM
 */
namespace GroupBot\Command;


use GroupBot\Telegram;
use GroupBot\Types\Command;

class click extends Command
{
    private $out;
    private $keyboard;

    public function insert_game($message_id)
    {
        $sql = 'INSERT INTO ms_games (chat_id, board, width, height) VALUES (:chat_id, :board, :width, :height)';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $game->chat_id);
        $query->bindValue(':width', $game->board->width);
        $query->bindValue(':height', $game->board->height);
        $query->bindValue(':board', $game->board->toDbString());

        return $query->execute();
    }

    public function update_game($message_id)
    {
        $sql = 'UPDATE ms_games 
                SET board = :board
                WHERE chat_id = :chat_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $game->chat_id);
        $query->bindValue(':board', $game->board->toDbString());

        return $query->execute();
    }

    public function delete_game($message_id)
    {
        $sql = 'DELETE FROM ms_games WHERE chat_id = :chat_id';

        $query = $this->db->prepare($sql);
        $query->bindParam(':chat_id', $game->chat_id);

        return $query->execute();
    }

    public function select_game($message_id)
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

    public function generateKeyboard()
    {
        $height = mt_rand(1,3);
        $width = mt_rand(1,3);
        $button = mt_rand(0, $height * $width - 1);

        $this->keyboard = [];
        for ($i = 0; $i < $height; $i++) {
            $row = [];
            for ($j = 0; $j < $width; $j++) {
                if ($i * $width + $j == $button) {
                    $row[] = [
                        'text' => emoji(0x1F535),
                        'callback_data' => "/click button"
                    ];
                } else {
                    $row[] = [
                        'text' => emoji(0x274C),
                        'callback_data' => "/click wrong"
                    ];
                }
            }
            $this->keyboard[] = $row;
        }
        return true;
    }

    public function main()
    {
        if ($this->Message->isCallback())
        {
            if ($this->isParam() && strcmp($this->getParam(), 'button') === 0) {
                $this->out = emoji(0x1F38C) . " *WINNER!*\n\n*" . $this->Message->User->getName() . "* clicked the button!";
                Telegram::edit_inline_message($this->Message->Chat->id, $this->Message->message_id, $this->out, $this->keyboard);
            }
        }
        else
        {
            $this->out = emoji(0x1F3C1) . " *Click the button to win!!*";
            $this->generateKeyboard();
            Telegram::talk_inline_keyboard($this->Message->Chat->id, $this->out, $this->keyboard);
        }
        return true;
    }
}