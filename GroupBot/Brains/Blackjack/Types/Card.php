<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 12/12/2015
 * Time: 8:43 PM
 */

namespace GroupBot\Brains\Blackjack\Types;


class Card
{
    public $rank;
    public $suite;

    public $value;

    public function __construct($rank, $suite)
    {
        $this->rank = $rank;
        $this->suite = $suite;
        $this->updateValue();
    }

    private function updateValue()
    {
        switch ($this->rank) {
            case 'A':
                $out = 'A';
                break;
            case 'J':
                $out = 10;
                break;
            case 'Q':
                $out = 10;
                break;
            case 'K':
                $out = 10;
                break;
            default:
                $out = $this->rank;
        }
        $this->value = $out;
    }
}