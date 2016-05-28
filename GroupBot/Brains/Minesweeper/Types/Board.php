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

    public function fromDbString($string, $width, $height)
    {
        $this->width = $width;
        $this->height = $height;
        $this->tiles = [];
        $tiles = explode('|', $string);
        $count = 0;
        foreach ($tiles as $tile) {
            $tmp = new Tile();
            $tmp->fromDbString($tile);
            $tmp->coordinate = new Coordinate($count % $this->width, floor($count / $this->width));
            $this->tiles[] = $tmp;
        }
        $this->calculateTileNumbers();
        return true;
    }

    public function getTile($x, $y)
    {
        if ($x < 0 || $x >= $this->width || $y < 0 || $y >= $this->height)
            return false;
        return $this->tiles[$y * $this->width + $x];
    }

    public function revealAdjacentEmptyTiles($x, $y)
    {
        foreach ([-1, 0, 1] as $i) {
            foreach ([-1, 0, 1] as $j) {
                if ($t = $this->getTile($x + $i, $y + $j)) {
                    if (!$t->revealed) {
                        $t->revealed = true;
                        if ($t->number == 0) $this->revealAdjacentEmptyTiles($x + $i, $y + $j);
                    }
                }
            }
        }
    }

    public function calculateTileNumbers()
    {
        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                $tile = $this->getTile($x, $y);
                if ($tile->mine) {
                    foreach ([-1, 0, 1] as $i) {
                        foreach ([-1, 0, 1] as $j) {
                            if ($t = $this->getTile($x + $i, $y + $j)) {
                                if (!$t->mine) $t->number++;
                            }
                        }
                    }
                }
            }
        }
    }

    public function generateNewBoard($no_mines)
    {
        if ($no_mines > $this->width * $this->height)
            return false;
        $this->tiles = [];
        $n = 0;
        while ($n < $this->width * $this->height) {
            $this->tiles[] = new Tile();
            $n++;
        }
        $n = 0;
        $no = mt_rand(0, $this->width * $this->height - 1);

        while ($n < $no_mines) {
            while ($this->tiles[$no]->mine) {
                $no = mt_rand(0, $this->width * $this->height - 1);
            }
            $this->tiles[$no]->mine = true;
            $n++;
        }
        $this->calculateTileNumbers();
        return true;
    }

    public function getBoardTelegramKeyboard($reveal = false)
    {
        $keyboard = [];
        $n = 0;
        for ($i = 0; $i < $this->height; $i++) {
            $row = [];
            for ($j = 0; $j < $this->width; $j++) {
                $row[] = [
                    'text' => $this->tiles[$n]->getTileEmoji($reveal),
                    'callback_data' => "/minesweeper reveal $j,$i"
                ];
                $n++;
            }
            $keyboard[] = $row;
        }
        return $keyboard;
    }
}