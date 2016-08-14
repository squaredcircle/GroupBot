<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 20/02/2016
 * Time: 12:59 PM
 */

namespace GroupBot\Brains\Todo\Types;


use GroupBot\Brains\Todo\Enums\VoteType;
use GroupBot\Types\User;

class UserVote
{
    /** @var  User */
    public $voter;

    /** @var  TodoItem */
    public $todo;

    /** @var  VoteType */
    public $vote;
    
    public function __construct(User $voter, TodoItem $todo, VoteType $vote)
    {
        $this->voter = $voter;
        $this->todo = $todo;
        $this->vote = $vote;
    }
}