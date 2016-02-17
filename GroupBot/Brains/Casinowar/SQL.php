<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 6:51 PM
 */

namespace GroupBot\Brains\Casinowar;


use GroupBot\Brains\CardGame\Enums\GameResult;
use GroupBot\Brains\CardGame\Enums\GameType;
use GroupBot\Brains\Casinowar\Enums\PlayerState;
use GroupBot\Brains\Casinowar\Types\Game;
use GroupBot\Brains\Casinowar\Types\Hand;
use GroupBot\Brains\Casinowar\Types\Player;
use GroupBot\Brains\Casinowar\Types\Stats;

class SQL extends \GroupBot\Brains\CardGame\SQL
{
    /**
     * @param $chat_id
     * @return bool
     */
    public function insert_game($chat_id)
    {
        $sql = 'INSERT INTO cw_games (chat_id, turn) VALUES (:chat_id, \'join\')';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $chat_id);

        return $query->execute();
    }

    /**
     * @param $game_id
     * @param Player $player
     * @return bool
     */
    public function insert_player($game_id, \GroupBot\Brains\CardGame\Types\Player $player)
    {
        $sql = 'INSERT INTO cw_players (user_id, user_name, game_id, cards, state, player_no, bet, free_bet, last_move_time)
                VALUES (:user_id, :user_name, :game_id, :cards, :state, :player_no, :bet, :free_bet, NOW())';
        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $player->user_id);
        $query->bindValue(':user_name', $player->user_name);
        $query->bindValue(':game_id', $game_id);
        $query->bindValue(':cards', $player->Hand->handToDbString());
        $query->bindValue(':state', $player->State);
        $query->bindValue(':player_no', $player->player_no);
        $query->bindValue(':bet', $player->bet);
        $query->bindValue(':free_bet', $player->free_bet);

        return $query->execute();
    }

    /**
     * @param Game $game
     * @return bool
     */
    public function update_game(\GroupBot\Brains\CardGame\Types\Game $game)
    {
        $sql = 'UPDATE cw_games SET turn = :turn WHERE id = :game_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':turn', $game->turn);
        $query->bindValue(':game_id', $game->game_id);

        return $query->execute();
    }

    /**
     * @param Player $player
     * @param $game_id
     * @return bool
     */
    public function update_player(\GroupBot\Brains\CardGame\Types\Player $player, $game_id)
    {
        $sql = 'UPDATE cw_players
                SET cards = :cards, state = :state, bet = :bet, player_no = :player_no, last_move_time = NOW()
                WHERE user_id = :user_id AND game_id = :game_id AND id = :id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':id', $player->id);
        $query->bindValue(':user_id', $player->user_id);
        $query->bindValue(':game_id', $game_id);
        $query->bindValue(':player_no', $player->player_no);
        $query->bindValue(':cards', $player->Hand->handToDbString());
        $query->bindValue(':state', $player->State);
        $query->bindValue(':bet', $player->bet);

        return $query->execute();
    }

    /**
     * @param $chat_id
     * @param $game_id
     * @return bool
     */
    public function delete_game($chat_id, $game_id)
    {
        $sql = 'DELETE FROM cw_games WHERE chat_id = :chat_id;
                DELETE FROM cw_players WHERE game_id = :game_id;';

        $query = $this->db->prepare($sql);
        $query->bindValue(':game_id', $game_id);
        $query->bindValue(':chat_id', $chat_id);

        return $query->execute();
    }

    /**
     * @param $chat_id
     * @return Game
     */
    public function select_game($chat_id)
    {
        $sql = 'SELECT id, turn FROM cw_games WHERE chat_id = :chat_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $chat_id);

        $query->execute();

        if ($query->rowCount()) {
            $game = $query->fetch();
            return new Game(new GameType(GameType::Casinowar), $chat_id, $game['id'], $game['turn'], $this->select_players($game['id']));
        }
        return false;
    }

    /**
     * @param $game_id
     * @return Player[]
     */
    public function select_players($game_id)
    {
        $sql = 'SELECT * FROM cw_players WHERE game_id = :game_id ORDER BY player_no ASC';

        $query = $this->db->prepare($sql);
        $query->bindValue(':game_id', $game_id);
        $query->execute();

        if ($query->rowCount()) {
            $Players = $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Brains\Casinowar\Types\Player');

            usort($Players, function($a, $b) {
                if ($a->player_no == $b->player_no) return 0;
                return $a->player_no < $b->player_no ? -1 : 1;
            });

            return $Players;
        }
        return false;
    }

    /**
     * @param $user_id
     * @return Stats
     */
    public function select_player_stats($user_id)
    {
        $sql = 'SELECT * FROM cw_stats WHERE user_id = :user_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $user_id);
        $query->execute();

        if ($query->rowCount()) {
            $query->setFetchMode(\PDO::FETCH_CLASS, 'GroupBot\Brains\Casinowar\Types\Stats');
            return $query->fetch();
        }
        return false;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function update_stats(\GroupBot\Brains\CardGame\Types\Player $player)
    {
        $sql = 'INSERT INTO cw_stats
                  (user_id, games_played, wins, losses, wars, surrenders, total_coin_bet, coin_won, coin_lost, free_bets)
                VALUES
                  (:user_id, 1, :wins, :losses, :wars, :surrenders, :bet, :coin_won, :coin_lost, :free_bets)
                ON DUPLICATE KEY UPDATE
                  games_played = games_played + 1,
                  wars = wars + :wars,
                  surrenders = surrenders + :surrenders,
                  total_coin_bet = total_coin_bet + :bet,
                  free_bets = free_bets + :free_bets,
                  coin_won = coin_won + :coin_won,
                  coin_lost = coin_lost + :coin_lost,
                  ';
        switch ($player->game_result) {
            case GameResult::Win:
                $sql .= 'wins = wins + 1';
                break;
            case GameResult::Loss:
                $sql .= 'losses = losses + 1';
                break;
            case GameResult::Draw:
                $sql .= 'draws = draws + 1';
                break;
        }

        $query = $this->db->prepare($sql);

        switch ($player->game_result) {
            case GameResult::Win:
                $query->bindValue(':wins', 1);
                $query->bindValue(':losses', 0);
                $query->bindValue(':draws', 0);
                break;
            case GameResult::Loss:
                $query->bindValue(':wins', 0);
                $query->bindValue(':losses', 1);
                $query->bindValue(':draws', 0);
                break;
            case GameResult::Draw:
                $query->bindValue(':wins', 0);
                $query->bindValue(':losses', 0);
                $query->bindValue(':draws', 1);
                break;
        }

        $query->bindValue(':wars', $player->no_wars);
        $query->bindValue(':surrenders', $player->no_surrenders);
        $query->bindValue(':bet', $player->free_bet ? 0 : $player->bet);
        $query->bindValue(':free_bets', $player->free_bet ? 1 : 0);

        if ($player->bet_result > 0) {
            $query->bindValue(':coin_won', $player->bet_result);
            $query->bindValue(':coin_lost', 0);
        } elseif ($player->bet_result < 0) {
            $query->bindValue(':coin_won', 0);
            $query->bindValue(':coin_lost', abs($player->bet_result));
        } else {
            $query->bindValue(':coin_won', 0);
            $query->bindValue(':coin_lost', 0);
        }

        $query->bindValue(':user_id', $player->user_id);

        return $query->execute();
    }
}