<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/09/2016
 * Time: 9:52 PM
 */

namespace GroupBot\Brains\CoinFlip;


class Game
{
    /** @var  int */
    public $message_id;

    /** @var  Player[] */
    public $players;

    public function getBettingPool()
    {
        return array_reduce($this->players, function($i, $player)
        {
            return $i += $player->bet;
        });
    }
}