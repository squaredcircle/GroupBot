<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 25/04/2016
 * Time: 3:02 PM
 */

namespace GroupBot\Brains\Minesweeper\Types;


class Coordinate
{
    /** @var  integer */
    public $x;
    
    /** @var  integer */
    public $y;

    public function __construct($x, $y)
    { 
        $this->x = $x;
        $this->y = $y;
    }
}