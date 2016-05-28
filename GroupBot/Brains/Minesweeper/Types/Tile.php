<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 25/04/2016
 * Time: 3:01 PM
 */

namespace GroupBot\Brains\Minesweeper\Types;


class Tile
{
    /** @var  Coordinate */
    public $coordinate;
    
    /** @var  boolean */
    public $mine = false;
    
    /** @var  boolean */
    public $revealed = false;
    
    /** @var  boolean */
    public $flagged = false;

    /** @var  integer */
    public $number = 0;

    public function __construct()
    {
    }

    public function toDbString()
    {
        $mine = $this->mine ? '1' :'0';
        $revealed = $this->revealed ? '1' :'0';
        $flagged = $this->flagged ? '1' :'0';
        return "$mine$revealed$flagged";
    }

    public function fromDbString($string)
    {
        $this->mine = strcmp($string[0], '1') === 0;
        $this->revealed = strcmp($string[1], '1') === 0;
        $this->flagged = strcmp($string[2], '1') === 0;
    }

    public function getTileEmoji($reveal = false)
    {
        if ($reveal) {
            if ($this->mine) return emoji(0x1F4A5);
            if ($this->number == 0) return emoji(0x2B1C);
            return emoji(0x0030 + $this->number) . emoji(0xFE0F) . emoji(0x20E3);
        }

        if ($this->revealed && $this->mine) return emoji(0x1F4A5);
        if ($this->revealed && $this->number == 0) return emoji(0x2B1C);
        if ($this->revealed) return emoji(0x0030 + $this->number) . emoji(0xFE0F) . emoji(0x20E3);
        if ($this->flagged) return emoji(0x1F6A9);
        return emoji(0x2B1B);
    }
}