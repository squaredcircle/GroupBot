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
    public $user_id;
    public $user_name;
    public $Hand;
    public $State;
    public $player_no;

    public function __construct($user_id, $user_name, $card_str, PlayerState $state, $player_no)
    {
        $this->user_id = $user_id;
        $this->user_name =  $user_name;
        $this->Hand = new Hand($card_str);
        $this->State = $state;
        $this->player_no = $player_no;
    }
}