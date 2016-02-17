<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 13/02/2016
 * Time: 1:12 AM
 */

namespace GroupBot\Brains\CardGame;


use GroupBot\Brains\CardGame\Enums\PlayerMove;
use GroupBot\Brains\Coin\Coin;
use GroupBot\Types\User;
use GroupBot\Brains\CardGame\Types\Game;

abstract class CardGame
{

    protected $chat_id, $user_id, $user_name, $bet, $free_bet;
    /**
     * @var Coin
     */
    protected $Coin;
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
     * @param User $User
     * @param $chat_id
     * @param PlayerMove $Move
     * @param $bet
     */
    public function __construct(User $User, $chat_id, PlayerMove $Move, $bet)
    {
        $this->chat_id = $chat_id;
        $this->user_id = $User->id;
        $this->user_name = $User->first_name;
        $this->bet = $bet;
        $this->free_bet = false;

        $this->SQL = $this->newSQL();
        $this->Talk = $this->newTalk($this->user_name);
        $this->Coin = new Coin();
        $this->Bets = new Bets($this->Talk);

        if (!$this->Game = $this->loadOrCreateGame($Move)) return false;
        $this->processPlayerMove($Move);
        return true;
    }

    /**
     * @return SQL
     */
    abstract protected function newSQL();

    /**
     * @param $user_name
     * @return Talk
     */
    abstract protected function newTalk($user_name);

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
        if (!$Game = $this->SQL->select_game($this->chat_id)) {
            $this->SQL->insert_game($this->chat_id);
            $Game = $this->SQL->select_game($this->chat_id);
            $Game->addDealer();
            if (!$this->Bets->checkPlayerBet($Game, $this->user_id, $this->bet)) return false;
            $this->bet = $this->Bets->bet;
            $this->free_bet = $this->Bets->free_bet;
            $player = $Game->addPlayer($this->user_id, $this->user_name, $this->bet, $this->free_bet);
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
            if ($this->Game->getCurrentPlayer()->user_id == $this->user_id
                && $Move != PlayerMove::JoinGame && $Move != PlayerMove::StartGame) {
                $this->processTurn($Move);
            } elseif ($player->user_id != $this->user_id
                && strtotime("-5 minutes") > strtotime($player->last_move_time)) {
                $this->user_id = $player->user_id;
                $this->user_name = $player->user_name;
                $this->Talk->turn_expired($player);
                $this->processTurn($this->newPlayerMove(PlayerMove::DefaultMove));
            } elseif ($Move == PlayerMove::JoinGame || $Move == PlayerMove::StartGame) {
                $this->Talk->game_status($this->Game);
            }
        }
        elseif (!$this->Game->isPlayerInGame($this->user_id))
        {
            if ($Move == PlayerMove::JoinGame ) {
                if (!$this->Bets->checkPlayerBet($this->Game, $this->user_id, $this->bet)) return false;
                $this->bet = $this->Bets->bet;
                $this->free_bet = $this->Bets->free_bet;
                $player = $this->Game->addPlayer($this->user_id, $this->user_name, $this->bet, $this->free_bet);
                $this->Talk->join_game($player);
            } elseif ($Move == PlayerMove::StartGame) {
                if (!$this->Bets->checkPlayerBet($this->Game, $this->user_id, $this->bet)) return false;
                $this->bet = $this->Bets->bet;
                $this->free_bet = $this->Bets->free_bet;
                $this->Game->addPlayer($this->user_id, $this->user_name, $this->bet, $this->free_bet);
                $this->Game->startGame();
                $this->Game->saveGame();
                $this->Talk->start_game($this->Game);
                if ($this->Game->areAllPlayersDone()) {
                    $this->finaliseGame();
                    $this->Game->endGame();
                }
            }
        }
        elseif ($this->Game->isPlayerInGame($this->user_id))
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
