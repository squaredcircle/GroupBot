<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 7:01 PM
 */

namespace GroupBot\Brains\CardGame\Enums;


abstract enum PlayerState
{
    case Dealer = 1;
    case Join = 2;
}