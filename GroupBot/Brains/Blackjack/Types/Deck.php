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

    public function __construct(Hand $dealt_cards = NULL)
    {
        $ranks  = ['A', 2, 3, 4, 5, 6, 7, 8, 9, 10, 'J', 'Q', 'K'];
        $suites = ['♠', '♥', '♦', '♣'];

        $this->Hand = new Hand();

        foreach ($suites as $suite) {
            foreach ($ranks as $rank) {
                $Card = new Card($rank, $suite);

                if (isset($dealt_cards)) {
                    if (!$dealt_cards->hasCard($Card)) {
                        $this->Hand->addCard($Card);
                    }
                } else {
                    $this->Hand->addCard($Card);
                }
            }
        }
    }

    public function dealCard()
    {
        $ranks  = ['A', 2, 3, 4, 5, 6, 7, 8, 9, 10, 'J', 'Q', 'K'];
        $suites = ['♠', '♥', '♦', '♣'];

        if ($this->Hand->hasCards()) {
            do {
                $Card = new Card($ranks[mt_rand(0, 12)], $suites[mt_rand(0, 3)]);
            } while (!$this->Hand->hasCard($Card));

            $this->Hand->removeCard($Card);
            return $Card;
        }
        return false;
    }
}
