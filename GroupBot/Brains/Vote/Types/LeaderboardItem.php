<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 20/02/2016
 * Time: 1:54 PM
 */

namespace GroupBot\Brains\Vote\Types;


use GroupBot\Types\User;

class LeaderboardItem
{
    /** @var  User */
    public $user;

    /** @var  int */
    public $vote_total;

    public function __construct($user, $vote_total)
    {
        $this->user = $user;
        $this->vote_total = $vote_total;
    }
}