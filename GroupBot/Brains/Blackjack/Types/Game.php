<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 6:42 PM
 */

namespace GroupBot\Brains\Blackjack\Types;


use GroupBot\Brains\Blackjack\Enums\PlayerState;
use GroupBot\Brains\CardGame\SQL;
use GroupBot\Brains\CardGame\Types\Deck;
use GroupBot\Brains\Coin\Coin;
use GroupBot\Brains\Coin\Enums\TransactionType;
use GroupBot\Brains\Coin\Types\Transaction;

class Game extends \GroupBot\Brains\CardGame\Types\Game
{
    /**
     * @var Player[]
     */
    public $Players = array();
    /**
     * @var Player
     */
    public $Dealer;

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
            $Player = new Player();
            $Player->construct('0', 'Dealer', new Hand(), new PlayerState(PlayerState::Dealer), -1, 0, false);
            $Player->Hand->addCard($this->Deck->dealCard());
            $this->Dealer = $Player;
            $this->SQL->insert_player($this->game_id, $Player);
            return true;
        }
        return false;
    }

    /**
     * @param $user_id
     * @param $user_name
     * @param $bet
     * @param $free_bet
     * @param int $split
     * @return bool|Player
     */
    public function addPlayer($user_id, $user_name, $bet, $free_bet, $split = 0)
    {
        $Coin = new Coin();

        if (!$this->isGameStarted()) {
            $Player = new Player();
            $Player->construct($user_id, $user_name, new Hand(), new PlayerState(PlayerState::Join), $this->getNumberOfPlayers(), $bet, $free_bet, NULL, NULL, $split);
            $Player->Hand->addCard($this->Deck->dealCard());
            $Player->Hand->addCard($this->Deck->dealCard());

            if ($Player->Hand->isBlackjack()) $Player->State = new PlayerState(PlayerState::BlackJack);

            $this->Players[] = $Player;
            $this->SQL->insert_player($this->game_id, $Player);

            if ($bet > 0 && !$free_bet) $Coin->Transact->performTransaction(new Transaction(
                NULL,
                $Coin->SQL->GetUserById($Player->user_id),
                $Coin->SQL->GetUserByName(COIN_TAXATION_BODY),
                abs($bet),
                new TransactionType(TransactionType::BlackjackBet)
            ));

            return $Player;
        } elseif ($split == 2) {
            foreach ($this->Players as $Player) {
                if ($Player->player_no > $this->getCurrentPlayer()->player_no) {
                    $Player->player_no++;
                    $this->savePlayer($Player);
                }
            }
            $Player = new Player();
            $Player->construct($user_id, $user_name, new Hand(), new PlayerState(PlayerState::Join), $this->getCurrentPlayer()->player_no + 1, $bet, $free_bet, NULL, NULL, 2);

            $Card = $this->getCurrentPlayer()->Hand->Cards[1];
            $this->getCurrentPlayer()->Hand->removeCard($Card);
            $Player->Hand->addCard($Card);
            $Player->Hand->addCard($this->Deck->dealCard());

            if ($Player->Hand->isBlackjack()) $Player->State = new PlayerState(PlayerState::BlackJack);
            $this->Players[] = $Player;
            $this->SQL->insert_player($this->game_id, $Player);

            if ($bet > 0) $Coin->Transact->performTransaction(new Transaction(
                NULL,
                $Coin->SQL->GetUserById($Player->user_id),
                $Coin->SQL->GetUserByName(COIN_TAXATION_BODY),
                abs($bet),
                new TransactionType(TransactionType::BlackjackBet)
            ));

            return $Player;
        }
        return false;
    }

    /**
     * @return SQL
     */
    protected function newSQL()
    {
        return new \GroupBot\Brains\Blackjack\SQL();
    }

    /**
     * @return Hand
     */
    protected function newHand()
    {
        return new Hand();
    }

    /**
     * @param $no_decks
     * @param Hand $dealt_cards
     * @return Deck
     */
    protected function newDeck($no_decks, \GroupBot\Brains\CardGame\Types\Hand $dealt_cards)
    {
        return new \GroupBot\Brains\Blackjack\Types\Deck($no_decks, $dealt_cards);
    }
}
