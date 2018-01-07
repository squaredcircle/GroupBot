<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/09/2016
 * Time: 9:43 PM
 */

namespace GroupBot\Brains\CoinFlip;


use GroupBot\Brains\Coin\Bets;
use GroupBot\Database\User;

class CoinFlip
{
    /** @var  \PDO */
    private $db;

    /** @var  Talk */
    public $Talk;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
        $this->Talk = new Talk();
    }

    /**
     * @return bool
     */
    private function flip()
    {
        return mt_rand(0,1);
    }

    public function joinGame(\GroupBot\Types\User $user, $bet, $message_id)
    {
        $game = $this->getGame($message_id);

        $Bets = new Bets($this->db);
        $bet = $Bets->checkPlayerBet($user, $bet, $game->getBettingPool(), 1);

        if ($bet === false)
        {
            $this->Talk->addMessage($Bets->response);
            return false;
        }

        $game->players[] = new Player($user, $bet);

        $this->Talk->join_game($user, $bet);

        return $this->saveGame($game);
    }

    public function startGame($message_id)
    {
        $game = $this->getGame($message_id);

        foreach ($game->players as $key => $player)
        {
            if ($key == count($game->players) - 1)
            {
                $count = array_reduce($game->players, function($i, $player)
                {
                    return $i += $player->choice ? 1 : 0;
                });

                if ($count == 0) $game->players[$key]->choice = true;
                elseif ($count == count($game->players)) $game->players[$key]->choice = false;
                else $game->players[$key]->choice = $this->flip();
            }
            else
            {
                $game->players[$key]->choice = $this->flip();
            }
            $this->Talk->player_choose($game->players[$key]);
        }

        $dealer = $this->flip();
        $this->Talk->dealer_flip($dealer);



        $this->deleteGame($message_id);
    }

    /**
     * @param $message_id
     * @return Game|bool
     */
    private function getGame($message_id)
    {
        $sql = 'SELECT * FROM cf_games WHERE message_id = :message_id ';

        $query = $this->db->prepare($sql);
        $query->bindValue(':message_id', $message_id);
        $query->execute();

        if ($query->rowCount()) {
            $results = $query->fetchAll();

            $game = new Game();
            $game->players = [];

            $DbUser = new User($this->db);

            foreach ($results as $player)
            {
                $user = $DbUser->getUserFromId($player['user_id']);
                $game->players[] = new Player($user, $player['bet']);
            }
            return $game;
        }
        return false;
    }

    private function saveGame(Game $game)
    {
        $sql = 'INSERT IGNORE INTO cf_games (message_id, user_id, bet) VALUES (:message_id, :user_id, :bet)';

        foreach ($game->players as $player)
        {
            $query = $this->db->prepare($sql);
            $query->bindValue(':message_id', $game->message_id);
            $query->bindValue(':user_id', $player->user->user_id);
            $query->bindValue(':bet', $player->bet);
            $query->execute();
        }
        return true;
    }

    private function deleteGame(Game $game)
    {
        $sql = 'DELETE FROM cf_games WHERE message_id = :message_id';
        $query = $this->db->prepare($sql);
        $query->bindValue(':message_id', $game->message_id);
        return $query->execute();
    }
}