<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 6:42 PM
 */

namespace GroupBot\Brains\Blackjack\Types;


use GroupBot\Brains\Blackjack\Database\Control;
use GroupBot\Brains\Blackjack\Enums\PlayerState;
use GroupBot\Brains\Coin\Coin;
use GroupBot\Brains\Coin\Enums\TransactionType;
use GroupBot\Brains\Coin\Types\Transaction;

class Game
{
    public $Players = array();
    public $Dealer;
    public $Deck;
    public $betting_pool;

    private $Coin;
    private $DbControl;
    private $chat_id;
    private $game_id;
    public $turn;

    public function __construct($chat_id, $game_id, $turn, $Players = NULL)
    {
        $this->DbControl = new Control($chat_id);
        $this->Coin = new Coin();

        $this->betting_pool = 0;
        $this->chat_id = $chat_id;
        $this->game_id = $game_id;

        $this->turn = $turn;
        if (isset($Players)) {
            foreach ($Players as $key => $player) {
                if ($player->user_id == '0') {
                    $this->Dealer = $player;
                    unset($Players[$key]);
                }
                $this->betting_pool += $player->bet;
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

    public function getPlayer($no)
    {
        foreach ($this->Players as $Player) {
            if ($Player->player_no == $no) {
                return $Player;
            }
        }
    }

    public function getNumberOfPlayers()
    {
        return count($this->Players);
    }

    public function areAllPlayersDone()
    {
        foreach ($this->Players as $Player) {
            if ($Player->State == PlayerState::Join || $Player->State == PlayerState::Hit) {
                return false;
            }
        }
        return true;
    }

    public function startGame()
    {
        $this->turn = -1;

        do {
            if (++$this->turn + 1 == $this->getNumberOfPlayers()) {
                return false;
            }
        } while ($this->getCurrentPlayer()->State == PlayerState::BlackJack);

        return true;
    }

    public function addDealer()
    {
        if (!$this->isGameStarted()) {
            $Player = new Player('0', 'Dealer', NULL, new PlayerState(PlayerState::Dealer), -1, 0, false, 0);
            $Player->Hand->addCard($this->Deck->dealCard());
            $this->Dealer = $Player;
            $this->DbControl->insert_player($Player, $this->game_id);
            return true;
        }
        return false;
    }

    public function addPlayer($user_id, $user_name, $bet, $free_bet, $split)
    {
        if (!$this->isGameStarted()) {
            $Player = new Player($user_id, $user_name, NULL, new PlayerState(PlayerState::Join), $this->getNumberOfPlayers(), $bet, $free_bet, $split);

            $Player->Hand->addCard($this->Deck->dealCard());
            $Player->Hand->addCard($this->Deck->dealCard());

            if ($Player->Hand->isBlackjack()) $Player->State = new PlayerState(PlayerState::BlackJack);

            $this->Players[] = $Player;
            $this->DbControl->insert_player($Player, $this->game_id);

            if ($bet > 0 && !$free_bet) $this->Coin->Transact->performTransaction(new Transaction(
                NULL,
                $this->Coin->SQL->GetUserById($Player->user_id),
                $this->Coin->SQL->GetUserByName(COIN_TAXATION_BODY),
                abs($bet),
                new TransactionType(TransactionType::BlackjackBet)
            ));

            return true;
        } elseif ($split == 2) {
            foreach ($this->Players as $Player) {
                if ($Player->player_no > $this->getCurrentPlayer()->player_no) {
                    $Player->player_no++;
                    $this->savePlayer($Player);
                }
            }
            $Player = new Player($user_id, $user_name, NULL, new PlayerState(PlayerState::Join),
                $this->getCurrentPlayer()->player_no + 1, $bet, $free_bet, 2);

            $Card = $this->getCurrentPlayer()->Hand->Cards[1];
            $this->getCurrentPlayer()->Hand->removeCard($Card);
            $Player->Hand->addCard($Card);
            $Player->Hand->addCard($this->Deck->dealCard());

            if ($Player->Hand->isBlackjack()) $Player->State = new PlayerState(PlayerState::BlackJack);
            $this->Players[] = $Player;
            $this->DbControl->insert_player($Player, $this->game_id);

            if ($bet > 0) $this->Coin->Transact->performTransaction(new Transaction(
                NULL,
                $this->Coin->SQL->GetUserById($Player->user_id),
                $this->Coin->SQL->GetUserByName(COIN_TAXATION_BODY),
                abs($bet),
                new TransactionType(TransactionType::BlackjackBet)
            ));

            return true;
        }
        return false;
    }

    public function savePlayer(Player $Player = NULL)
    {
        if (!isset($Player)) {
            $Player = $this->getCurrentPlayer();
        }
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
