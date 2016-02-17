<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 13/02/2016
 * Time: 1:20 PM
 */

namespace GroupBot\Brains\CardGame\Types;


abstract class Stats
{
    public $user_id;
    public $games_played, $wins, $losses, $draws;
    public $total_coin_bet, $coin_won, $coin_lost, $free_bets;
}