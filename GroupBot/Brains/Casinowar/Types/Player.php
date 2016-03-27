<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 6:55 PM
 */

namespace GroupBot\Brains\Casinowar\Types;


use GroupBot\Brains\Casinowar\Enums\PlayerState;

class Player extends \GroupBot\Brains\CardGame\Types\Player
{
    /** @var Hand  */
    public $Hand;
    public $no_surrenders = 0;
    public $no_wars = 0;
    /** @var PlayerState  */
    public $State;

    public function construct($user_id, $user_name, \GroupBot\Brains\CardGame\Types\Hand $hand, \GroupBot\Brains\CardGame\Enums\PlayerState $playerState,
                              $player_no, $bet, $free_bet, $id = NULL, $last_move_time = NULL,
                              $no_wars = 0, $no_surrenders = 0)
    {
        $this->user_id = $user_id;
        $this->user_name =  $user_name;
        $this->Hand = $hand;
        $this->State = $playerState;
        $this->player_no = $player_no;
        $this->bet = $bet;
        $this->free_bet = $free_bet;

        $this->id = $id;
        $this->last_move_time = $last_move_time;

        $this->no_wars = $no_wars;
        $this->no_surrenders = $no_surrenders;
    }

    public function isAtWar()
    {
        return ($this->no_wars > 0);
    }

    /**
     * @return Hand
     */
    protected function newHand()
    {
        return new Hand();
    }

    /**
     * @param $state
     * @return \GroupBot\Brains\CardGame\Enums\PlayerState
     */
    protected function newPlayerState($state)
    {
        return new PlayerState($state);
    }
}