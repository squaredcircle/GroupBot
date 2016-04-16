<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 16/04/16
 * Time: 2:50 PM
 */

namespace GroupBot\Brains\Weather;


class Weather
{
    public function __construct()
    {
        
    }

    public static function realtime()
    {
        $json = file_get_contents('http://www.bom.gov.au/fwo/IDW60901/IDW60901.94608.json');
        $weather = json_decode($json);

        $header = $weather->observations->header[0];
        return $weather->observations->data[0];
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
    public static function sunrise($latitude = -31.9535, $longitude = 115.8570, $zenith = 90 + 50/60, $gmt = 8)
    {
        $sunrise =  date_sunrise(time(), SUNFUNCS_RET_STRING, $latitude, $longitude, $zenith, $gmt);
        $sunset =  date_sunset(time(), SUNFUNCS_RET_STRING, $latitude, $longitude, $zenith, $gmt);
        return new Sunrise($latitude, $longitude, $zenith, $gmt, "Perth", $sunrise, $sunset);
    }

    /**
     * @return bool|UV
     */
    public function uv_index()
    {
        $xml = file_get_contents("http://www.arpansa.gov.au/uvindex/realtime/xml/uvvalues.xml");
        $arpansa = new \SimpleXMLElement($xml);

        foreach ($arpansa as $location) {
            if (strcmp($location->name, 'per') ===0) {
                $descr = $this->uv_description($location->index);
                return new UV($location->index, $descr[0], $descr[1]);
            }
        }

        return false;
    }

    /**
     * @param $index
     * @return array
     */
    private function uv_description($index)
    {
        if ($index <= 2.5) return ["green", "low"];
        if ($index <= 5.5) return ["yellow", "moderate"];
        if ($index <= 7.5) return ["orange", "high"];
        if ($index <= 10.5) return ["red", "very high"];
        return ["purple", "extreme"];
    }

}