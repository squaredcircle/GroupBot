<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 12/12/2015
 * Time: 8:43 PM
 */

namespace GroupBot\Brains\Blackjack\Types;


class Card extends \GroupBot\Brains\CardGame\Types\Card
{
    protected function updateValue()
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