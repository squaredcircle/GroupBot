<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 7:01 PM
 */

namespace GroupBot\Brains\CardGame\Enums;


abstract class PlayerState extends \SplEnum
{
    const Dealer = 1;
    const Join = 2;
}