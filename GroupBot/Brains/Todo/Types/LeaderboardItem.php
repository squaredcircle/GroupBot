<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 20/02/2016
 * Time: 1:54 PM
 */

namespace GroupBot\Brains\Todo\Types;


class LeaderboardItem
{
    /** @var  TodoItem */
    public $item;

    /** @var  int */
    public $vote_total;

    public function __construct($item, $vote_total)
    {
        $this->item = $item;
        $this->vote_total = $vote_total;
    }
}