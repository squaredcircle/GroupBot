<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 6:51 PM
 */

namespace GroupBot\Brains\Blackjack\Database;


use GroupBot\Brains\Blackjack\Types\Player;

class SQL
{
    private $db;

    public function __construct()
    {
        $DbControl = new \GroupBot\Base\DbControl();
        $this->db = $DbControl->getObject();
    }

    public function insert_game($chat_id, $turn)
    {
        $sql = 'INSERT INTO bj_games (chat_id, turn) VALUES (:chat_id, :turn)';

        $query = $this->db->prepare($sql);
        $query->bindValue(':turn', $turn);
        $query->bindValue(':chat_id', $chat_id);

        return $query->execute();
    }

    public function insert_player($game_id, $user_id, $user_name, $card_str, $player_state, $player_no, $bet, $free_bet, $split)
    {
        $sql = 'INSERT INTO bj_players (user_id, user_name, game_id, cards, state, player_no, bet, free_bet, split)
                VALUES (:user_id, :user_name, :game_id, :cards, :state, :player_no, :bet, :free_bet, :split)';

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $user_id);
        $query->bindValue(':user_name', $user_name);
        $query->bindValue(':game_id', $game_id);
        $query->bindValue(':cards', $card_str);
        $query->bindValue(':state', $player_state);
        $query->bindValue(':player_no', $player_no);
        $query->bindValue(':bet', $bet);
        $query->bindValue(':free_bet', $free_bet);
        $query->bindValue(':split', $split);

        return $query->execute();
    }

    public function updatePlayer($id, $user_id, $game_id, $player_no, $card_str, $player_state, $bet, $split)
    {
        $sql = 'UPDATE bj_players
                SET cards = :cards, state = :state, bet = :bet, split = :split, player_no = :player_no
                WHERE user_id = :user_id AND game_id = :game_id AND id = :id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':id', $id);
        $query->bindValue(':user_id', $user_id);
        $query->bindValue(':game_id', $game_id);
        $query->bindValue(':player_no', $player_no);
        $query->bindValue(':cards', $card_str);
        $query->bindValue(':state', $player_state);
        $query->bindValue(':bet', $bet);
        $query->bindValue(':split', $split);

        return $query->execute();
    }

    public function updateGame($game_id, $turn)
    {
        $sql = 'UPDATE bj_games SET turn = :turn WHERE id = :game_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':turn', $turn);
        $query->bindValue(':game_id', $game_id);

        return $query->execute();
    }

    public function delete($chat_id, $game_id)
    {
        $sql = 'DELETE FROM bj_games WHERE chat_id = :chat_id;
                DELETE FROM bj_players WHERE game_id = :game_id;';

        $query = $this->db->prepare($sql);
        $query->bindValue(':game_id', $game_id);
        $query->bindValue(':chat_id', $chat_id);

        return $query->execute();
    }

    public function select_game($chat_id)
    {
        $sql = 'SELECT id, turn FROM bj_games WHERE chat_id = :chat_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $chat_id);

        $query->execute();

        if ($query->rowCount()) {
            return $query->fetch();
        } else {
            return false;
        }
    }

    public function select_players($game_id)
    {
        $sql = 'SELECT id, user_id, user_name, cards, state, player_no, bet, free_bet, split FROM bj_players WHERE game_id = :game_id ORDER BY player_no ASC';

        $query = $this->db->prepare($sql);
        $query->bindValue(':game_id', $game_id);

        $query->execute();

        if ($query->rowCount()) {
            return $query->fetchAll();
        } else {
            return false;
        }
    }

    public function select_player_stats_today($user_id)
    {
        $sql = 'SELECT * FROM bj_stats_today WHERE user_id = :user_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $user_id);

        $query->execute();

        if ($query->rowCount()) {
            return $query->fetch();
        } else {
            return false;
        }
    }

    public function update_stats(Player $Player)
    {
        $this->update_stats_table('bj_stats', $Player);
        $this->update_stats_table('bj_stats_today', $Player);
    }

    private function update_stats_table($table, Player $Player)
    {
        $sql = 'INSERT INTO ' . $table  . '
                  (user_id, games_played, wins, losses, draws, hits, stands, blackjacks, splits, doubledowns, surrenders, total_coin_bet, coin_won, coin_lost, free_bets)
                VALUES
                  (:user_id, 1, :wins, :losses, :draws, :hits, :stands, :blackjacks, :splits, :doubledowns, :surrenders, :bet, :coin_won, :coin_lost, :free_bets)
                ON DUPLICATE KEY UPDATE
                  games_played = games_played + 1,
                  hits = hits + :hits,
                  stands = stands + :stands,
                  blackjacks = blackjacks + :blackjacks,
                  splits = splits + :splits,
                  doubledowns = doubledowns + :doubledowns,
                  surrenders = surrenders + :surrenders,
                  total_coin_bet = total_coin_bet + :bet,
                  free_bets = free_bets + :free_bets,
                  coin_won = coin_won + :coin_won,
                  coin_lost = coin_lost + :coin_lost,
                  ';
        switch ($Player->game_result) {
            case "win":
                $sql .= 'wins = wins + 1';
                break;
            case "lose":
                $sql .= 'losses = losses + 1';
                break;
            case "draw":
                $sql .= 'draws = draws + 1';
                break;
        }

        $query = $this->db->prepare($sql);

        switch ($Player->game_result) {
            case "win":
                $query->bindValue(':wins', 1);
                $query->bindValue(':losses', 0);
                $query->bindValue(':draws', 0);
                break;
            case "lose":
                $query->bindValue(':wins', 0);
                $query->bindValue(':losses', 1);
                $query->bindValue(':draws', 0);
                break;
            case "draw":
                $query->bindValue(':wins', 0);
                $query->bindValue(':losses', 0);
                $query->bindValue(':draws', 1);
                break;
        }

        $query->bindValue(':hits', $Player->no_hits);
        $query->bindValue(':stands', $Player->no_stands);
        $query->bindValue(':blackjacks', $Player->no_blackjacks);
        $query->bindValue(':splits', $Player->no_splits);
        $query->bindValue(':doubledowns', $Player->no_doubledowns);
        $query->bindValue(':surrenders', $Player->no_surrenders);
        $query->bindValue(':bet', $Player->free_bet ? 0 : $Player->bet);
        $query->bindValue(':free_bets', $Player->free_bet ? 1 : 0);

        if ($Player->bet_result > 0) {
            $query->bindValue(':coin_won', $Player->bet_result);
            $query->bindValue(':coin_lost', 0);
        } elseif ($Player->bet_result < 0) {
            $query->bindValue(':coin_won', 0);
            $query->bindValue(':coin_lost', abs($Player->bet_result));
        } else {
            $query->bindValue(':coin_won', 0);
            $query->bindValue(':coin_lost', 0);
        }

        $query->bindValue(':user_id', $Player->user_id);

        return $query->execute();
    }
}