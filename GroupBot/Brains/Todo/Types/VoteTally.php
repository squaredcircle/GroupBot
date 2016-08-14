<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 20/02/2016
 * Time: 12:59 PM
 */

namespace GroupBot\Brains\Todo\Types;


class VoteTally
{
    /** @var  int */
    public $up;

    /** @var  int */
    public $down;

    /** @var  int */
    public $neutral;

    /** @var  int */
    public $total;
}