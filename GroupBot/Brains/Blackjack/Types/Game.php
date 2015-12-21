<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 6:42 PM
 */

namespace GroupBot\Brains\Blackjack\Types;

use GroupBot\Base\Telegram;
use GroupBot\Brains\Blackjack\Database\Control;
use GroupBot\Brains\Blackjack\Enums\PlayerState;
use GroupBot\Brains\Coin;

class Game
{
    public $Players = array();
    public $Dealer;
    public $Deck;

    private $DbControl;
    private $chat_id;
    private $game_id;
    public $turn;

    public function __construct($chat_id, $game_id, $turn, $Players = NULL)
    {
        $this->DbControl = new Control($chat_id);
        $this->chat_id = $chat_id;
        $this->game_id = $game_id;
        $this->turn = $turn;
        if (isset($Players)) {
            foreach ($Players as $key => $player) {
                if ($player->user_id == '0') {
                    $this->Dealer = $player;
                    unset($Players[$key]);
                }
            }
            $this->Players = array_values($Players);
        }
        $this->Deck = $this->buildDeck();
    }

    public function isGameStarted()
    {
        return ($this->turn != 'join');
    }

    public function isPlayerInGame($user_id)
    {
        foreach ($this->Players as $Player) {
            if ($Player->user_id == $user_id) return true;
        }
        return false;
    }

    public function getCurrentPlayer()
    {
        return $this->Players[$this->turn];
    }

    public function getNumberOfPlayers()
    {
        return count($this->Players);
    }

    public function startGame()
    {
        $this->turn = 0;
    }

    public function addDealer()
    {
        if (!$this->isGameStarted()) {
            $Player = new Player('0', 'Dealer', NULL, new PlayerState(PlayerState::Dealer), -1, 0);
            $Player->Hand->addCard($this->Deck->dealCard());
            $this->Dealer = $Player;
            $this->DbControl->insert_player($Player, $this->game_id);
            return true;
        }
        return false;
    }

    public function addPlayer($user_id, $user_name, $bet)
    {
        if (!$this->isGameStarted()) {
            $Player = new Player($user_id, $user_name, NULL, new PlayerState(PlayerState::Join), $this->getNumberOfPlayers(), $bet);
            $Player->Hand->addCard($this->Deck->dealCard());
            $Player->Hand->addCard($this->Deck->dealCard());
            if ($Player->Hand->isBlackjack()) $Player->State = new PlayerState(PlayerState::BlackJack);
            $this->Players[] = $Player;
            $this->DbControl->insert_player($Player, $this->game_id);


            if ($bet > 0) {
                $Coin = new Coin();
                $Coin->performTransaction($Player->user_id, TAXATION_BODY, abs($Player->bet), new Telegram());
            }

            return true;
        }
        return false;
    }

    public function savePlayer()
    {
        $Player = $this->getCurrentPlayer();
        $this->DbControl->updatePlayer($Player, $this->game_id);
    }

    public function saveGame()
    {
        $this->DbControl->updateGame($this->turn, $this->game_id);
    }

    public function endGame()
    {
        $this->DbControl->delete($this->game_id);
    }

    private function buildDeck()
    {
        $Hand = new Hand();
        foreach ($this->Players as $Player) {
            foreach ($Player->Hand->Cards as $Card) {
                $Hand->addCard($Card);
            }
        }
        $Deck = new Deck(4, $Hand);
        return $Deck;
    }
}