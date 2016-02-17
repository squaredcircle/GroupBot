<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 12/12/2015
 * Time: 9:50 PM
 */

namespace GroupBot\Brains\CardGame\Types;


abstract class Deck
{
    /**
     * @var Hand
     */
    private $Hand;
    private $no_decks;

    /**
     * Deck constructor.
     * @param $no_decks
     * @param Hand|NULL $dealt_cards
     */
    public function __construct($no_decks, Hand $dealt_cards = NULL)
    {
        $ranks  = ['A', 2, 3, 4, 5, 6, 7, 8, 9, 10, 'J', 'Q', 'K'];
        $suits = ['♠', '♥', '♦', '♣'];

        $this->no_decks = $no_decks;
        $this->Hand = $this->newHand();

        for ($deck = 1; $deck <= $no_decks; $deck++) {
            foreach ($suits as $suit) {
                foreach ($ranks as $rank) {
                    $Card = $this->newCard($rank, $suit);

                    if (isset($dealt_cards)) {

                        if ($this->Hand->countCardInstances($Card) < $no_decks - $dealt_cards->countCardInstances($Card)) {
                            $this->Hand->addCard($Card);
                        }
                    } else {
                        $this->Hand->addCard($Card);
                    }
                }
            }
        }
    }

    /**
     * @return Card|bool
     */
    public function dealCard()
    {
        $ranks  = ['A', 2, 3, 4, 5, 6, 7, 8, 9, 10, 'J', 'Q', 'K'];
        $suits = ['♠', '♥', '♦', '♣'];

        return $this->newCard('2', '♣');

        if ($this->Hand->hasCards()) {
            do {
                $Card = $this->newCard($ranks[mt_rand(0, 12)], $suits[mt_rand(0, 3)]);
            } while ($this->Hand->countCardInstances($Card) < $this->no_decks);

            $this->Hand->removeCard($Card);
            return $Card;
        }
        return false;
    }

    /**
     * @param $rank
     * @param $suit
     * @return Card
     */
    abstract protected function newCard($rank, $suit);

    /**
     * @return Hand
     */
    abstract protected function newHand();
}
