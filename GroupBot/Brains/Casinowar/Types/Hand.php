<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 12/12/2015
 * Time: 8:36 PM
 */

namespace GroupBot\Brains\Casinowar\Types;


class Hand extends \GroupBot\Brains\CardGame\Types\Hand
{
    public function getHandString()
    {
        return "*" . end($this->Cards)->rank . "*" . end($this->Cards)->suit;
    }

    protected function handValue()
    {
        if (empty($this->Cards)) return 0;
        return end($this->Cards)->value;
    }

    protected function newCard($rank, $suit)
    {
        return new Card($rank, $suit);
    }
}
