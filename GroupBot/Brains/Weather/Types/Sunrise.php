<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 16/04/16
 * Time: 2:31 PM
 */

namespace GroupBot\Brains\Weather;


class Sunrise
{
    public $latitude;
    public $longitude;
    public $zenith;
    public $gmt;

    public $location_name;

    public $sunrise;
    public $sunset;

    public function __construct($latitude, $longitude, $zenith, $gmt, $location_name, $sunrise, $sunset)
    {

        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->zenith = $zenith;
        $this->gmt = $gmt;

        $this->location_name = $location_name;

        $this->sunrise = $sunrise;
        $this->sunset = $sunset;
    }
}