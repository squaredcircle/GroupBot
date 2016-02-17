<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 12/12/2015
 * Time: 8:43 PM
 */

namespace GroupBot\Brains\Casinowar\Types;


class Card extends \GroupBot\Brains\CardGame\Types\Card
{
    protected function updateValue()
    {
        switch ($this->rank) {
            case 'A':
                $out = 14;
                break;
            case 'J':
                $out = 11;
                break;
            case 'Q':
                $out = 12;
                break;
            case 'K':
                $out = 13;
                break;
            default:
                $out = $this->rank;
        }
        $this->value = $out;
    }
}