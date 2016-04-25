<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 25/04/2016
 * Time: 3:00 PM
 */

namespace GroupBot\Brains\Minesweeper\Types;


class Board
{
    /** @var  Tile[] */
    public $tiles;

    /** @var  integer */
    public $width;

    /** @var  integer */
    public $height;

    public function toDbString()
    {
        $out = '';
        foreach ($this->tiles as $tile) {
            $out .= $tile->toDbString() . '|';
        }
        return rtrim($out, "|");
    }
}