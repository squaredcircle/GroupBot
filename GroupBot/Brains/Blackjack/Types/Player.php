<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 6:55 PM
 */

namespace GroupBot\Brains\Blackjack\Types;


use GroupBot\Brains\Blackjack\Enums\PlayerState;
use GroupBot\Types\User;

class Player extends \GroupBot\Brains\CardGame\Types\Player
{
    /** @var Hand  */
    public $Hand;
    /** @var PlayerState  */
    public $State;
    public $split = 0;
    public $no_hits, $no_stands, $no_blackjacks, $no_splits, $no_doubledowns, $no_surrenders;

    public function isSplit()
    {
        return $this->split != 0;
    }

    public function construct(User $user, \GroupBot\Brains\CardGame\Types\Hand $hand, \GroupBot\Brains\CardGame\Enums\PlayerState $playerState,
                              $player_no, $bet, $free_bet, $id = NULL, $last_move_time = NULL,
                              $split = 0, $no_hits = 0, $no_stands = 0, $no_blackjacks = 0, $no_splits = 0, $no_doubledowns = 0, $no_surrenders = 0)
    {
        $this->user= $user;
        $this->Hand = $hand;
        $this->State = $playerState;
        $this->player_no = $player_no;
        $this->bet = $bet;
        $this->free_bet = $free_bet;

        $this->id = $id;
        $this->last_move_time = $last_move_time;

        $this->split = $split;
        $this->no_hits = $no_hits;
        $this->no_stands = $no_stands;
        $this->no_blackjacks = $no_blackjacks;
        $this->no_splits = $no_splits;
        $this->no_doubledowns = $no_doubledowns;
        $this->no_surrenders = $no_surrenders;
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
     * @return PlayerState
     */
    protected function newPlayerState($state)
    {
        return new PlayerState($state);
    }
}