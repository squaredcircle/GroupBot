<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 12/12/2015
 * Time: 8:36 PM
 */

namespace GroupBot\Brains\CardGame\Types;


abstract class Hand
{
    /** @var Card[] */
    public $Cards;
    public $Value;

    public function __construct($card_array = array())
    {
        $this->Cards = $card_array;
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

    public function handToDbString()
    {
        $out = '';
        foreach ($this->Cards as $Card) {
            $out .= ',' . $Card->cardToDbString();
        }

        return substr($out, 1);
    }

    public function __toString()
    {
        $out = '';
        if (!$this->hasCards()) return $out;

        foreach ($this->Cards as $Card) {
            $out .= ',' . (string)$Card;
        }

        return substr($out, 1);
    }

    public function handFromDbString($cards)
    {
        $card_array = explode(",", $cards);
        $Cards = array();

        foreach($card_array as $i) {
            $card = $this->newCard(NULL,NULL);
            $card->cardFromDbString($i);
            $Cards[] = $card;
        }

        $this->Cards = $Cards;
        $this->Value = $this->handValue();
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

    abstract protected function handValue();

    /**
     * @param $rank
     * @param $suit
     * @return Card
     */
    abstract protected function newCard($rank, $suit);
}
