<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 13/12/2015
 * Time: 9:33 AM
 */

namespace GroupBot\Brains\Blackjack\Database;


use GroupBot\Brains\Blackjack\Types\Game;
use GroupBot\Brains\Blackjack\Types\Player;

class Control
{
    private $DBSQL;
    private $Convert;
    private $chat_id;

    public function __construct($chat_id)
    {
        $this->DBSQL = new SQL();
        $this->Convert = new Convert();
        $this->chat_id = $chat_id;
    }

    public function insert_game()
    {
        return $this->DBSQL->insert_game($this->chat_id, 'join');
    }

    public function insert_player(Player $Player, $game_id)
    {
        return $this->DBSQL->insert_player(
            $game_id,
            $Player->user_id,
            $Player->user_name,
            $this->Convert->handToString($Player->Hand),
            $this->Convert->stateToString($Player->State),
            $Player->player_no,
            $Player->bet,
            $Player->free_bet,
            $Player->split
        );
    }

    public function updatePlayer(Player $Player, $game_id)
    {
        return $this->DBSQL->updatePlayer(
            $Player->db_id,
            $Player->user_id,
            $game_id,
            $Player->player_no,
            $this->Convert->handToString($Player->Hand),
            $this->Convert->stateToString($Player->State),
            $Player->bet,
            $Player->split
        );
    }

    public function updateGame($turn, $game_id)
    {
        return $this->DBSQL->updateGame($game_id, $turn);
    }

    public function delete($game_id)
    {
        return $this->DBSQL->delete($this->chat_id, $game_id);
    }

    public function updateStats($Players)
    {
        foreach ($Players as $Player) {
            $this->DBSQL->update_stats($Player);
        }
        return true;
    }

    public function getStats($user_id)
    {
        return $this->DBSQL->select_player_stats_today($user_id);
    }

    public function getGame()
    {
        if ($game = $this->DBSQL->select_game($this->chat_id)) {
            return new Game($this->chat_id, $game['id'], $game['turn'], $this->getPlayers($game['id']));
        }
        return false;
    }

    private function getPlayers($game_id)
    {
        $players = $this->DBSQL->select_players($game_id);

        $Players = array();
        if (!empty($players)) {
            foreach ($players as $player) {
                $tmp = new Player($player['user_id'], $player['user_name'], $this->Convert->handFromString($player['cards']), $this->Convert->stateFromString($player['state']), $player['player_no'], $player['bet'], $player['free_bet'], $player['split']);
                $tmp->setDbId($player['id']);
                $Players[] = $tmp;
            }

            usort($Players, function($a, $b) {
                if ($a->player_no == $b->player_no) return 0;
                return $a->player_no < $b->player_no ? -1 : 1;
            });
        }

        return $Players;
    }
}
