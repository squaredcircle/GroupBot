<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 6:55 PM
 */

namespace GroupBot\Brains\CardGame\Types;


use GroupBot\Brains\CardGame\Enums\GameResult;
use GroupBot\Brains\CardGame\Enums\PlayerState;

abstract class Player
{
    public $id;
    public $user_id;
    public $user_name;
    /** @var Hand  */
    public $Hand;
    /** @var PlayerState  */
    public $State;

    protected $cards;
    protected $state;

    public $player_no;
    public $bet;
    public $free_bet;

    public $bet_result = 0;
    /** @var  GameResult */
    public $game_result;
    public $last_move_time;

    public function __construct()
    {
        if (!isset($this->Hand) && isset($this->cards)) {
            $this->Hand = $this->newHand();
            $this->Hand->handFromDbString($this->cards);
        }
        if (!isset($this->State) && isset($this->state))
            $this->State = $this->newPlayerState($this->state);
    }

    abstract public function construct($user_id, $user_name, Hand $hand, PlayerState $playerState, $player_no, $bet, $free_bet, $id = NULL, $last_move_time = NULL);

    /**
     * @return Hand
     */
    abstract protected function newHand();

    /**
     * @param $state
     * @return PlayerState
     */
    abstract protected function newPlayerState($state);

    public function hasBet()
    {
        return $this->bet > 0;
    }
}