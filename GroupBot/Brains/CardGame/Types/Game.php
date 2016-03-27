<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 6:42 PM
 */

namespace GroupBot\Brains\CardGame\Types;


use GroupBot\Brains\CardGame\Bets;
use GroupBot\Brains\CardGame\Enums\GameType;
use GroupBot\Brains\CardGame\SQL;
use GroupBot\Types\User;

abstract class Game
{
    /**
     * @var Player[]
     */
    public $Players = array();
    /**
     * @var Player
     */
    public $Dealer;
    /**
     * @var Deck
     */
    public $Deck;

    /**
     * @var GameType
     */
    public $GameType;

    /**
     * @var SQL
     */
    protected $SQL;

    public $betting_pool, $chat_id, $game_id, $turn;

    /**
     * Game constructor.
     * @param \PDO $db
     * @param GameType $gameType
     * @param $chat_id
     * @param $game_id
     * @param $turn
     * @param Player[]|NULL $Players
     */
    public function __construct(\PDO $db, GameType $gameType, $chat_id, $game_id, $turn, $Players = NULL)
    {
        $this->SQL = $this->newSQL($db);
        $this->GameType = $gameType;

        $this->betting_pool = 0;
        $this->chat_id = $chat_id;
        $this->game_id = $game_id;

        $this->turn = $turn;
        if (isset($Players) && $Players) {
            foreach ($Players as $key => $player) {
                if ($player->user->user_id == '-1') {
                    $this->Dealer = $player;
                    unset($Players[$key]);
                }
                $this->betting_pool += $player->bet;
            }
            $this->Players = array_values($Players);
        }
        $this->Deck = $this->buildDeck();
    }

    /**
     * @return bool
     */
    public function isGameStarted()
    {
        return ($this->turn != 'join');
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function isPlayerInGame($user_id)
    {
        foreach ($this->Players as $Player) {
            if ($Player->user->user_id == $user_id) return true;
        }
        return false;
    }

    /**
     * @return Player
     */
    public function getCurrentPlayer()
    {
        return $this->Players[$this->turn];
    }

    /**
     * @param $no
     * @return Player|bool
     */
    public function getPlayer($no)
    {
        foreach ($this->Players as $Player) {
            if ($Player->player_no == $no) {
                return $Player;
            }
        }
        return false;
    }

    /**
     * @return int
     */
    public function getNumberOfPlayers()
    {
        return count($this->Players);
    }

    /**
     * @return bool
     */
    abstract public function areAllPlayersDone();

    /**
     * @return bool
     */
    abstract public function startGame();

    /**
     * @return bool
     */
    abstract public function addDealer();

    /**
     * @param User $user
     * @param Bets $bets
     * @param $bet
     * @param $free_bet
     * @return bool|Player
     */
    abstract public function addPlayer(User $user, Bets $bets, $bet, $free_bet);

    /**
     * @param Player|NULL $Player
     */
    public function savePlayer(Player $Player = NULL)
    {
        if (!isset($Player)) {
            $Player = $this->getCurrentPlayer();
        }
        $this->SQL->update_player($Player, $this->game_id);
    }

    /**
     * @return bool
     */
    public function saveGame()
    {
        return $this->SQL->update_game($this);
    }

    /**
     * @return bool
     */
    public function endGame()
    {
        foreach ($this->Players as $player) $this->SQL->update_stats($player);
        $this->SQL->delete_game($this->chat_id, $this->game_id);
        return true;
    }

    /**
     * @return Deck
     */
    private function buildDeck()
    {
        $Hand = $this->newHand();
        foreach ($this->Players as $Player) {
            foreach ($Player->Hand->Cards as $Card) {
                $Hand->addCard($Card);
            }
        }
        $Deck = $this->newDeck(4, $Hand);
        return $Deck;
    }

    /**
     * @param \PDO $db
     * @return SQL
     */
    abstract protected function newSQL(\PDO $db);

    /**
     * @return Hand
     */
    abstract protected function newHand();

    /**
     * @param $no_decks
     * @param Hand $dealt_cards
     * @return Deck
     */
    abstract protected function newDeck($no_decks, Hand $dealt_cards);
}
