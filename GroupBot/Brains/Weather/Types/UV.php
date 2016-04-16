<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 16/04/16
 * Time: 2:30 PM
 */

namespace GroupBot\Brains\Weather\Types;


class UV
{
    public $value;
    public $colour_code;
    public $description;

    public function __construct($value, $colour_code, $description)
    {
        $this->value = $value;
        $this->colour_code = $colour_code;
        $this->description = $description;
    }
}