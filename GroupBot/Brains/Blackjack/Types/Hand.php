<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 12/12/2015
 * Time: 8:36 PM
 */

namespace GroupBot\Brains\Blackjack\Types;


class Hand
{
    public $Cards;
    public $Value;

    public function __construct($card_array = array())
    {
        $this->Cards = $this->Cards = $card_array;
        $this->Value = $this->handValue();
    }

    public function addCard(Card $Card)
    {
        $this->Cards[] = $Card;
        $this->Value = $this->handValue();
    }

    public function removeCard(Card $Card)
    {
        if ($this->hasCard($Card)) {
            $i = array_search($Card, $this->Cards);
            unset($this->Cards[$i]);
            $this->Value = $this->handValue();
            return true;
        }
        return false;
    }

    public function getHandString()
    {
        $out = '';
        foreach ($this->Cards as $Card) {
            $out .= "*" . $Card->rank . "*" . $Card->suit . ", ";
        }
        return substr($out, 0, -2);
    }

    public function countCardInstances(Card $Card)
    {
        $map = function(Card $c) {return $c->rank . $c->suit;};
        $count = array_count_values(array_map($map, $this->Cards));
        $key = $Card->rank . $Card->suit;
        return array_key_exists($key, $count) ? $count[$key] : 0;
    }

    public function hasCard(Card $Card)
    {
        return in_array($Card, $this->Cards);
    }

    public function hasCards()
    {
        return !empty($this->Cards);
    }

    public function size()
    {
        return count($this->Cards);
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

    private function handValue()
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
}
