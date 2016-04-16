<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 16/04/16
 * Time: 2:30 PM
 */

namespace GroupBot\Brains\Weather\Types;


class Realtime
{
    public $name;
    public $state;

    public $air_temp;

    public function __construct($name, $state, $air_temp)
    {
        $this->name = $name;
        $this->state = $state;
        $this->air_temp = $air_temp;
    }
}