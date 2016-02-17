<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 12/12/2015
 * Time: 8:36 PM
 */

namespace GroupBot\Brains\Blackjack\Types;


class Hand extends \GroupBot\Brains\CardGame\Types\Hand
{
    public function canSplit()
    {
        return (count($this->Cards) == 2 && $this->Cards[0]->value == $this->Cards[1]->value);
    }

    public function isBust()
    {
        return ($this->Value > 21);
    }

    public function isTwentyOne()
    {
        return ($this->Value == 21);
    }

    public function isDealerDone()
    {
        return ($this->Value >= 17);
    }

    public function isBlackjack()
    {
        return ($this->size() == 2 && $this->Value == 21);
    }

    protected function handValue()
    {
        if (empty($this->Cards)) return 0;

        $aces = 0;
        $value = 0;

        foreach ($this->Cards as $Card) {
            if ($Card->value == 'A')
                $aces++;
            else
                $value += $Card->value;
        }

        if (($aces > 0) && (21 >= $value + 11 + ($aces - 1)))
            return $value + 11 + ($aces - 1);
        else
            return $value + $aces;
    }

    protected function newCard($rank, $suit)
    {
        return new Card($rank, $suit);
    }
}
