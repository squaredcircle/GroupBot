<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 12/12/2015
 * Time: 8:43 PM
 */

namespace GroupBot\Brains\CardGame\Types;


abstract class Card
{
    public $rank;
    public $suit;

    public $value;

    public function __construct($rank, $suit)
    {
        $this->rank = $rank;
        $this->suit = $suit;
        $this->updateValue();
    }

    public function __toString()
    {
        switch ($this->rank) {
            case 'A':
                $rank = 0;
                break;
            case 'J':
                $rank = 10;
                break;
            case 'Q':
                $rank = 11;
                break;
            case 'K':
                $rank = 12;
                break;
            default:
                $rank = $this->rank - 1;
        }

        switch ($this->suit) {
            case '♠':
                $suit = 0;
                break;
            case '♥':
                $suit = 1;
                break;
            case '♦':
                $suit = 2;
                break;
            case '♣':
                $suit = 3;
                break;
            default:
                return false;
        }

        return $suit * 13 + $rank;
    }

    public function cardToDbString()
    {
        switch ($this->rank) {
            case 'A':
                $rank = 0;
                break;
            case 'J':
                $rank = 10;
                break;
            case 'Q':
                $rank = 11;
                break;
            case 'K':
                $rank = 12;
                break;
            default:
                $rank = $this->rank - 1;
        }

        switch ($this->suit) {
            case '♠':
                $suit = 0;
                break;
            case '♥':
                $suit = 1;
                break;
            case '♦':
                $suit = 2;
                break;
            case '♣':
                $suit = 3;
                break;
            default:
                return false;
        }

        return $suit * 13 + $rank;
    }

    public function cardFromDbString($card)
    {
        switch ($card % 13) {
            case 0:
                $rank = 'A';
                break;
            case 10:
                $rank = 'J';
                break;
            case 11:
                $rank = 'Q';
                break;
            case 12:
                $rank = 'K';
                break;
            default:
                $rank = $card % 13 + 1;
        }

        switch (floor($card / 13)) {
            case 0:
                $suit = '♠';
                break;
            case 1:
                $suit = '♥';
                break;
            case 2:
                $suit = '♦';
                break;
            case 3:
                $suit = '♣';
                break;
            default:
                $suit = '';
        }

        $this->rank = $rank;
        $this->suit = $suit;
        $this->updateValue();
    }

    /*
     * case 'A', 'J', 'Q', 'K'
     */
    abstract protected function updateValue();
}