<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 12/12/2015
 * Time: 9:50 PM
 */

namespace GroupBot\Brains\Blackjack\Types;


class Deck
{
    private $Hand;
    private $no_decks;

    public function __construct($no_decks, Hand $dealt_cards = NULL)
    {
        $ranks  = ['A', 2, 3, 4, 5, 6, 7, 8, 9, 10, 'J', 'Q', 'K'];
        $suits = ['♠', '♥', '♦', '♣'];

        $this->no_decks = $no_decks;
        $this->Hand = new Hand();

        for ($deck = 1; $deck <= $no_decks; $deck++) {
            foreach ($suits as $suit) {
                foreach ($ranks as $rank) {
                    $Card = new Card($rank, $suit);

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

    public function dealCard()
    {
        $ranks  = ['A', 2, 3, 4, 5, 6, 7, 8, 9, 10, 'J', 'Q', 'K'];
        $suits = ['♠', '♥', '♦', '♣'];

        if ($this->Hand->hasCards()) {
            do {
                $Card = new Card($ranks[mt_rand(0, 12)], $suits[mt_rand(0, 3)]);
            } while ($this->Hand->countCardInstances($Card) < $this->no_decks);

            $this->Hand->removeCard($Card);
            return $Card;
        }
        return false;
    }
}
