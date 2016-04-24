<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 24/04/2016
 * Time: 12:21 PM
 */

namespace GroupBot\Brains\RussianRoulette;


class RussianRoulette
{
    /** @var  SQL */
    private $SQL;

    /** @var  Game */
    private $Game;

    private $chat_id, $user_id;

    public function __construct(\PDO $db, $chat_id, $user_id)
    {
        $this->SQL = new SQL($db);
        $this->chat_id = $chat_id;
        $this->user_id = $user_id;
        $this->Game = $this->SQL->select_game($chat_id);
    }

    public function isLoaded()
    {
        return !is_bool($this->Game);
    }

    public function reload()
    {
        if ($this->isLoaded()) {
            $this->SQL->delete_game($this->Game->chat_id);
        }
        $this->Game = new Game();
        $this->Game->construct($this->chat_id, mt_rand(0, 5), 0);
        return $this->SQL->insert_game($this->Game);
    }

    public function trigger()
    {
        if ($this->Game->current_chamber == $this->Game->bullet_chamber) {
            $this->SQL->delete_game($this->chat_id);
            $this->SQL->update_stats($this->user_id, true);
            return true;
        } else {
            $this->Game->current_chamber++;
            $this->SQL->update_game($this->Game);
            $this->SQL->update_stats($this->user_id);
            return false;
        }
    }
}