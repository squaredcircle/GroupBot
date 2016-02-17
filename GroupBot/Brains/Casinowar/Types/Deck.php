<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 12/12/2015
 * Time: 9:50 PM
 */

namespace GroupBot\Brains\Casinowar\Types;


class Deck extends \GroupBot\Brains\CardGame\Types\Deck
{
    protected function newCard($rank, $suit)
    {
        return new Card($rank, $suit);
    }

    protected function newHand()
    {
        return new Hand();
    }
}
