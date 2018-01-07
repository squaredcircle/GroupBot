<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/09/2016
 * Time: 9:53 PM
 */

namespace GroupBot\Brains\CoinFlip;


use GroupBot\Types\User;

class Player
{
    /** @var  User */
    public $user;

    /** @var  double */
    public $bet;

    /** @var  bool */
    public $choice;

    public function __construct(User $user, $bet)
    {
        $this->user = $user;
        $this->bet = $bet;
    }
}