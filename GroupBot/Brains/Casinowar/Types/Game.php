<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 6:42 PM
 */

namespace GroupBot\Brains\Casinowar\Types;



use GroupBot\Brains\Casinowar\Enums\PlayerState;
use GroupBot\Brains\Casinowar\SQL;
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

    public function isWar()
    {
        foreach ($this->Players as $player) {
            if ($player->State == PlayerState::War) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function areAllPlayersDone()
    {
        foreach ($this->Players as $Player) {
            if ($Player->State == PlayerState::Draw) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function startGame()
    {
        $this->turn = -1;

        do {
            if (++$this->turn + 1 == $this->getNumberOfPlayers()) {
                return false;
            }
        } while ($this->getCurrentPlayer()->State != PlayerState::Draw);

        return true;
    }

    /**
     * @return bool
     */
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
     * @return Player|bool
     */
    public function addPlayer($user_id, $user_name, $bet, $free_bet)
    {
        if (!$this->isGameStarted()) {
            $Player = new Player();
            $Player->construct($user_id, $user_name, new Hand(), new PlayerState(PlayerState::Join), $this->getNumberOfPlayers(), $bet, $free_bet);
            $Player->Hand->addCard($this->Deck->dealCard());

            if ($Player->Hand->Value > $this->Dealer->Hand->Value) $Player->State = new PlayerState(PlayerState::Win);
            if ($Player->Hand->Value == $this->Dealer->Hand->Value) $Player->State = new PlayerState(PlayerState::Draw);
            if ($Player->Hand->Value < $this->Dealer->Hand->Value) $Player->State = new PlayerState(PlayerState::Lose);

            $this->Players[] = $Player;
            $this->SQL->insert_player($this->game_id, $Player);

            $Coin = new Coin();
            if ($bet > 0 && !$free_bet) $Coin->Transact->performTransaction(new Transaction(
                NULL,
                $Coin->SQL->GetUserById($Player->user_id),
                $Coin->SQL->GetUserByName(COIN_TAXATION_BODY),
                abs($bet),
                new TransactionType(TransactionType::CasinoWarBet)
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
        return new SQL();
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
        return new Deck($no_decks, $dealt_cards);
    }
}