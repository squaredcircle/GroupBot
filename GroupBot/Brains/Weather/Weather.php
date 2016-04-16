<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 16/04/16
 * Time: 2:50 PM
 */

namespace GroupBot\Brains\Weather;


use GroupBot\Brains\Weather\Types\Realtime;
use GroupBot\Brains\Weather\Types\Sunrise;
use GroupBot\Brains\Weather\Types\UV;

class Weather
{
    public static $icon_map =
        [
            1 => 0x2600,    // Sunny
            2 => 0x1F319,   // Clear (moon)
            3 => 0x26C5,    // Mostly sunny
            4 => 0x2601,    // Cloudy
            6 => 0x2601,    // Hazy (cloud)
            8 => 0x2614,    // Light rain (umbrella)
            9 => 0x1F4A8,   // Windy (dash symbol)
            10 => 0x1F301,   // Foggy
            11 => 0x2614,    // Shower (umbrella)
            12 => 0x1F327,    // Rain (cloud with rain)
            13 => 0x1F4A8,   // Dusty (dash symbol)
            14 => 0x2744,   // Frost (snowflake)
            15 => 0x2744,   // Snow (snowflake)
            16 => 0x1F329,  // Storm (cloud with lightning)
            17 => 0x2614,   // Light shower (umbrella)
            18 => 0x1F327,   // Heavy shower (cloud with rain),
            19 => 0x1F300   // Cyclone
        ];

    public static function realtime()
    {
        $json = file_get_contents('http://www.bom.gov.au/fwo/IDW60901/IDW60901.94608.json');
        $weather = json_decode($json);

        $header = $weather->observations->header[0];
        $data = $weather->observations->data[0];

        return new Realtime($header->name, $header->state, $data->air_temp);
    }

    public static function forecast()
    {
        $xml = file_get_contents("ftp://ftp2.bom.gov.au/anon/gen/fwo/IDW12300.xml");
        $bom = new \SimpleXMLElement($xml);

        $forecast = [];

        foreach ($bom->forecast[0] as $area) {
            $index = 0;
            foreach ($area as $forecast_period) {
                if (++$index > 5) break;

                $start_time = (string)$forecast_period['start-time-local'];
                $dayOfWeek = \GroupBot\Libraries\Carbon::parse($start_time)->format('l');

                if ($area['aac'] == 'WA_ME001' || $area['aac'] == 'WA_PT053') {
                    foreach ($forecast_period->element as $value) {
                        $type = (string)$value['type'];
                        $forecast[$dayOfWeek][$type] = (string)$value;
                    }

                    foreach ($forecast_period->text as $value) {
                        $type = (string)$value['type'];
                        $forecast[$dayOfWeek][$type] = (string)$value;
                    }
                }
            }
        }
        return $forecast;
    }

    public function forecast_old()
    {
        $handle = fopen('ftp://ftp2.bom.gov.au/anon/gen/fwo/IDA00100.dat', "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $arr = explode("#", $line);
                if (strcmp($arr[0], "Perth") === 0) {
                    return [$arr[6], $arr[7]];
                }
            }

            fclose($handle);
        }

        return false;
    }

    /**
     * @param float $latitude
     * @param float $longitude
     * @param int $zenith
     * @param int $gmt
     * @return Sunrise
     * Defaults to Perth, Western Australia
     */
    public static function sunrise($latitude = -31.9535, $longitude = 115.8570, $zenith = 90 + 50 / 60, $gmt = 8)
    {
        $sunrise = date_sunrise(time(), SUNFUNCS_RET_STRING, $latitude, $longitude, $zenith, $gmt);
        $sunset = date_sunset(time(), SUNFUNCS_RET_STRING, $latitude, $longitude, $zenith, $gmt);
        return new Sunrise($latitude, $longitude, $zenith, $gmt, "Perth", $sunrise, $sunset);
    }

    /**
     * @return bool|UV
     */
    public static function uv_index()
    {
        $xml = file_get_contents("http://www.arpansa.gov.au/uvindex/realtime/xml/uvvalues.xml");
        $arpansa = new \SimpleXMLElement($xml);

        foreach ($arpansa as $location) {
            if (strcmp($location->name, 'per') === 0) {
                $descr = self::uv_description($location->index);
                return new UV($location->index, $descr[0], $descr[1]);
            }
        }

        return false;
    }

    /**
     * @param $index
     * @return array
     */
    private static function uv_description($index)
    {
        if ($index <= 2.5) return ["green", "low"];
        if ($index <= 5.5) return ["yellow", "moderate"];
        if ($index <= 7.5) return ["orange", "high"];
        if ($index <= 10.5) return ["red", "very high"];
        return ["purple", "extreme"];
    }

}