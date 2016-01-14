<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 6:55 PM
 */

namespace GroupBot\Brains\Blackjack\Types;


use GroupBot\Brains\Blackjack\Enums\PlayerState;

class Player
{
    public $db_id;
    public $user_id;
    public $user_name;
    public $Hand;
    public $State;
    public $player_no;
    public $bet;
    public $free_bet;
    public $split;

    public $no_hits = 0, $no_stands = 0, $no_blackjacks = 0, $no_splits = 0, $no_doubledowns = 0, $no_surrenders = 0;
    public $bet_result = 0;
    public $game_result;

    public function __construct($user_id, $user_name, $card_str, PlayerState $state, $player_no, $bet, $free_bet, $split)
    {
        $this->user_id = $user_id;
        $this->user_name =  $user_name;
        $this->Hand = new Hand($card_str);
        $this->State = $state;
        $this->player_no = $player_no;
        $this->bet = $bet;
        $this->free_bet = $free_bet;
        $this->split = $split;
    }

    public function setDbId($id)
    {
        $this->db_id = $id;
    }

    public function isSplit()
    {
        return $this->split != 0;
    }

    public function hasBet()
    {
        return $this->bet > 0;
    }
}