<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 15/11/2015
 * Time: 6:46 PM
 */

namespace GroupBot\Types;


class Location
{
    /** @var  double */
    public $latitude;
    
    /** @var  double */
    public $longitude;

    public function getSQLString()
    {
        return "$this->latitude $this->longitude";
    }

    public function createFromSQL($location)
    {
        $loc = explode(" ", $location);
        $this->latitude = $loc[0];
        $this->longitude = $loc[1];
    }
}