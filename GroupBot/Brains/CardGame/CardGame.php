<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 13/02/2016
 * Time: 1:12 AM
 */

namespace GroupBot\Brains\CardGame;


use GroupBot\Brains\CardGame\Enums\PlayerMove;
use GroupBot\Brains\Coin\Money\Transact;
use GroupBot\Types\Chat;
use GroupBot\Types\User;
use GroupBot\Brains\CardGame\Types\Game;

abstract class CardGame
{

    protected $bet, $free_bet;

    /** @var  Chat */
    protected $chat;

    /** @var  User */
    protected $user;

    /** @var \PDO  */
    protected $db;

    /**
     * @var Transact
     */
    protected $Transact;
    /**
     * @var SQL
     */
    protected $SQL;
    /**
     * @var Bets
     */
    protected $Bets;
    /**
     * @var Talk
     */
    public $Talk;

    /**
     * @var Game
     */
    public $Game;

    /**
     * CardGame constructor.
     * @param \PDO $db
     * @param User $user
     * @param Chat $chat
     * @param PlayerMove $Move
     * @param $bet
     * @internal param $chat_id
     */
    public function __construct(\PDO $db, User $user, Chat $chat, PlayerMove $Move, $bet)
    {
        $this->chat = $chat;
        $this->user = $user;
        $this->bet = $bet;
        $this->free_bet = false;

        $this->db = $db;
        $this->SQL = $this->newSQL($db);
        $this->Talk = $this->newTalk();
        $this->Transact = new Transact($db);
        $this->Bets = new Bets($this->Talk, $db);

        if (!$this->Game = $this->loadOrCreateGame($Move)) return false;
        $this->processPlayerMove($Move);
        return true;
    }

    /**
     * @param \PDO $db
     * @return SQL
     */
    abstract protected function newSQL(\PDO $db);

    /**
     * @param $user_name
     * @return Talk
     */
    abstract protected function newTalk();

    /**
     * @param $playerMove
     * @return PlayerMove
     */
    abstract protected function newPlayerMove($playerMove);


    /**
     * @param PlayerMove $Move
     * @return bool|Game
     */
    private function loadOrCreateGame(PlayerMove $Move)
    {
        if (!$Game = $this->SQL->select_game($this->chat->id)) {
            $this->SQL->insert_game($this->chat->id);
            $Game = $this->SQL->select_game($this->chat->id);
            $Game->addDealer($this->db);
            if (!$this->Bets->checkPlayerBet($Game, $this->user, $Game->Dealer->user, $this->bet)) return false;
            $this->bet = $this->Bets->bet;
            $this->free_bet = $this->Bets->free_bet;
            $player = $Game->addPlayer($this->user, $this->Bets, $this->bet, $this->free_bet);
            if ($Move == PlayerMove::JoinGame) $this->Talk->join_game($player);
        }
        return $Game;
    }

    /**
     * @param PlayerMove $Move
     * @return bool
     */
    private function processPlayerMove(PlayerMove $Move)
    {
        if ($this->Game->isGameStarted())
        {
            $player = $this->Game->getCurrentPlayer();
            if ($this->Game->getCurrentPlayer()->user->user_id == $this->user->user_id
                && $Move != PlayerMove::JoinGame && $Move != PlayerMove::StartGame)
            {
                $this->processTurn($Move);
            }
            elseif ($player->user->user_id != $this->user->user_id
                && strtotime("-5 minutes") > strtotime($player->last_move_time))
            {
                $this->user->user_id = $player->user->user_id;
                $this->Talk->turn_expired($player);
                $this->processTurn($this->newPlayerMove(PlayerMove::DefaultMove));
            }
            elseif ($Move == PlayerMove::JoinGame || $Move == PlayerMove::StartGame)
            {
                $this->Talk->game_status($this->Game);
            }
        }
        elseif (!$this->Game->isPlayerInGame($this->user->user_id))
        {
            if ($Move == PlayerMove::JoinGame )
            {
                if (!$this->Bets->checkPlayerBet($this->Game, $this->user, $this->Game->Dealer->user, $this->bet)) return false;
                $this->bet = $this->Bets->bet;
                $this->free_bet = $this->Bets->free_bet;
                $player = $this->Game->addPlayer($this->user, $this->Bets, $this->bet, $this->free_bet);
                $this->Talk->join_game($player);
            }
            elseif ($Move == PlayerMove::StartGame)
            {
                if (!$this->Bets->checkPlayerBet($this->Game, $this->user, $this->Game->Dealer->user, $this->bet)) return false;
                $this->bet = $this->Bets->bet;
                $this->free_bet = $this->Bets->free_bet;
                $this->Game->addPlayer($this->user, $this->Bets, $this->bet, $this->free_bet);
                $this->Game->startGame();
                $this->Game->saveGame();
                $this->Talk->start_game($this->Game);
                if ($this->Game->areAllPlayersDone()) {
                    $this->finaliseGame();
                    $this->Game->endGame();
                }
            }
        }
        elseif ($this->Game->isPlayerInGame($this->user->user_id))
        {
            if ($Move == PlayerMove::StartGame) {
                $this->Game->startGame();
                $this->Game->saveGame();
                $this->Talk->start_game($this->Game);
                if ($this->Game->areAllPlayersDone()) {
                    $this->finaliseGame();
                    $this->Game->endGame();
                }
            } elseif ($Move == PlayerMove::QuitGame) {
                $this->Game->endGame();
            } elseif ($Move != PlayerMove::JoinGame) {
                $this->Talk->pre_game_status($this->Game);
            }
        }
        return true;
    }

    /**
     * @param PlayerMove $playerMove
     * @return bool
     */
    private function processTurn(PlayerMove $playerMove)
    {
        $this->processTurnActions($playerMove);
        $this->Game->savePlayer();
        if ($this->cyclePlayer()) {
            $this->Game->saveGame();
            $this->Talk->next_turn($this->Game);
        } else {
            $this->finaliseGame();
            $this->Game->endGame();
        }
        return true;
    }

    /**
     * @param PlayerMove $Move
     * @return bool
     */
    abstract protected function processTurnActions(PlayerMove $Move);

    /**
     * @return bool
     */
    abstract protected function cyclePlayer();

    /**
     * @return bool
     */
    abstract protected function finaliseGame();
}
