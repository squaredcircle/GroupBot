<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 6:51 PM
 */

namespace GroupBot\Brains\CardGame;


use GroupBot\Brains\CardGame\Types\Game;
use GroupBot\Brains\CardGame\Types\Player;
use GroupBot\Brains\CardGame\Types\Stats;
use GroupBot\Database\DbConnection;

abstract class SQL extends DbConnection
{
    /**
     * @param $chat_id
     * @return bool
     */
    abstract public function insert_game($chat_id);

    /**
     * @param $game_id
     * @param Player $player
     * @return bool
     */
    abstract public function insert_player($game_id, Player $player);

    /**
     * @param Game $game
     * @return bool
     */
    abstract public function update_game(Game $game);

    /**
     * @param Player $player
     * @param $game_id
     * @return bool
     */
    abstract public function update_player(Player $player, $game_id);

    /**
     * @param $chat_id
     * @param $game_id
     * @return bool
     */
    abstract public function delete_game($chat_id, $game_id);

    /**
     * @param $chat_id
     * @return Game
     */
    abstract public function select_game($chat_id);

    /**
     * @param $game_id
     * @return Player[]
     */
    abstract public function select_players($game_id);

    /**
     * @param $user_id
     * @return Stats
     */
    abstract public function select_player_stats($user_id);

    /**
     * @param Player $player
     * @return bool
     */
    abstract public function update_stats(Player $player);
}