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
    public $mine;
    
    /** @var  boolean */
    public $revealed;
    
    /** @var  boolean */
    public $flagged;

    /** @var  integer */
    public $number;

    public function toDbString()
    {
        $mine = $this->mine ? '1' :'0';
        $revealed = $this->revealed ? '1' :'0';
        $flagged = $this->flagged ? '1' :'0';
        return "$mine,$revealed,$flagged";
    }
}