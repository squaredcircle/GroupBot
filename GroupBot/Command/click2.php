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
use GroupBot\Types\User;

class c2player
{
    /** @var  User */
    public $user;

    /** @var  integer */
    public $message_id;

    /** @var  integer */
    public $rank;

    public function __construct(User $user, $message_id, $rank)
    {
        $this->user = $user;
        $this->message_id = $message_id;
        $this->rank = $rank;
    }
}

class click2 extends Command
{
    private $out;
    private $keyboard;

    /** @var  c2player[] */
    private $players;

    private function insert_player(c2player $player)
    {
        $sql = 'INSERT INTO c2_games (message_id, user_id, rank) VALUES (:message_id, :user_id, :rank)';

        $query = $this->db->prepare($sql);
        $query->bindValue(':message_id', $player->message_id);
        $query->bindValue(':user_id', $player->user->user_id);
        $query->bindValue(':rank', $player->rank);
        return $query->execute();
    }

    private function delete_game($message_id)
    {
        $sql = 'DELETE FROM c2_games WHERE message_id = :message_id';

        $query = $this->db->prepare($sql);
        $query->bindParam(':message_id', $message_id);

        return $query->execute();
    }

    private function select_game($message_id)
    {
        $sql = 'SELECT * FROM c2_games WHERE message_id = :message_id';

        $query = $this->db->prepare($sql);
        $query->bindParam(':message_id', $message_id);

        $query->execute();

        if ($query->rowCount()) {
            $results = $query->fetchAll();
            $out = [];
            $DbUser = new \GroupBot\Database\User($this->db);
            foreach ($results as $player) {
                if ($user = $DbUser->getUserFromId($player['user_id'])) {
                    $out[] = new c2player($user, $player['message_id'], $player['rank']);
                }
            }
            usort($out, function ($a, $b) {
                if ($a->rank == $b->rank)
                    return 0;
                if ($a->rank > $b->rank)
                    return 1;
                return -1;
            });
            return $out;
        }
        return false;
    }

    private function generateKeyboard()
    {
        $height = mt_rand(1, 3);
        $width = mt_rand(1, 3);
        $button = mt_rand(0, $height * $width - 1);

        $this->keyboard = [];
        for ($i = 0; $i < $height; $i++) {
            $row = [];
            for ($j = 0; $j < $width; $j++) {
                if ($i * $width + $j == $button) {
                    $row[] = [
                        'text' => emoji(0x1F535),
                        'callback_data' => "/click2 button"
                    ];
                } else {
                    $row[] = [
                        'text' => emoji(0x274C),
                        'callback_data' => "/click2 wrong"
                    ];
                }
            }
            $this->keyboard[] = $row;
        }
        return true;
    }

    private function isPlayerInGame($user_id)
    {
        if (!$this->players)
            return false;
        foreach ($this->players as $player) {
            if ($player->user->user_id == $user_id)
                return true;
        }
        return false;
    }

    private function buttonClicked($message_id, User $user)
    {
        if (!$this->isPlayerInGame($user->user_id)) {
            if ($this->players) {
                $player = new c2player($user, $message_id, count($this->players) + 1);
                $this->insert_player($player);
                $this->players[] = $player;
            } else {
                $player = new c2player($user, $message_id, 1);
                $this->insert_player($player);
            }
        }
    }

    private function display()
    {
        $this->out = emoji(0x1F38C) . " *The race is on!!*";

        foreach ($this->players as $player) {
            $this->out .= "\n`   `• `" . addOrdinalNumberSuffix($player->rank) . "` *" . $player->user->getName() . "!*";
        }
    }

    public function main()
    {
        if ($this->Message->isCallback()) {
            $this->players = $this->select_game($this->Message->message_id);

            if ($this->isParam() && strcmp($this->getParam(), 'button') === 0) {
                if (!$this->isPlayerInGame($this->Message->User->user_id)) {
                    $this->buttonClicked($this->Message->message_id, $this->Message->User);
                    $this->display();
                    $this->generateKeyboard();
                    Telegram::edit_inline_message($this->Message->Chat->id, $this->Message->message_id, $this->out, $this->keyboard);
                }
            }
        } else {
            $this->out = emoji(0x1F3C1) . " *Click the button to win!!!*";
            $this->generateKeyboard();
            Telegram::talk_inline_keyboard($this->Message->Chat->id, $this->out, $this->keyboard);
        }
        return true;
    }
}