<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 20/02/2016
 * Time: 12:59 PM
 */

namespace GroupBot\Brains\Vote\Types;


use GroupBot\Base\DbControl;
use GroupBot\Brains\Vote\Enums\VoteType;
use GroupBot\Types\User;

class UserVote
{
    /** @var  User */
    public $voter;

    /** @var  User */
    public $voted_for;

    /** @var  VoteType */
    public $vote;
    
    public function construct(User $voter, User $voted_for, VoteType $vote)
    {
        $this->voter = $voter;
        $this->voted_for = $voted_for;
        $this->vote = $vote;
    }
}