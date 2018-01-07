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
use Carbon\Carbon;

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

    public static $locations =
        [
            'brisbane',
            'melbourne',
            'sydney',
            'canberra',
            'hobart',
            'darwin',
            'perth',
            'adelaide'
        ];

    private static $realtime =
        [
            'brisbane' => 'http://www.bom.gov.au/fwo/IDQ60901/IDQ60901.94576.json',
            'melbourne' => 'http://www.bom.gov.au/fwo/IDV60901/IDV60901.95936.json',
            'sydney' => 'http://www.bom.gov.au/fwo/IDN60901/IDN60901.94768.json',
            'canberra' => 'http://www.bom.gov.au/fwo/IDN60903/IDN60903.94926.json',
            'hobart' => 'http://www.bom.gov.au/fwo/IDT60901/IDT60901.94970.json',
            'darwin' => 'http://www.bom.gov.au/fwo/IDD60901/IDD60901.94120.json',
            'perth' => 'http://www.bom.gov.au/fwo/IDW60901/IDW60901.94608.json',
            'adelaide' => 'http://www.bom.gov.au/fwo/IDS60901/IDS60901.94675.json'
        ];

    private static $forecast =
        [
            'brisbane' => 'IDQ10095',
            'melbourne' => 'IDV10450',
            'sydney' => 'IDN10064',
            'canberra' => 'IDN10035',
            'hobart' => 'IDT13600',
            'darwin' => 'IDD10150',
            'adelaide' => 'IDS10034',
            'perth' => 'IDW12300'
        ];

    private static $forecast_subcodes =
        [
            'brisbane' => ['QLD_ME001', 'QLD_PT001'],
            'melbourne' => ['VIC_ME001', 'VIC_PT042'],
            'sydney' => ['NSW_ME001', 'NSW_PT131'],
            'canberra' => ['NSW_ME001', 'NSW_PT027'],
            'hobart' => ['TAS_ME001', 'TAS_PT021'],
            'darwin' => ['NT_ME001', 'NT_PT001'],
            'adelaide' => ['SA_ME001', 'SA_PT001'],
            'perth' => ['WA_ME001', 'WA_PT053']
        ];

    private static $coordinates =
        [
            'brisbane' => [27.4710, 153.0234, 10],
            'melbourne' => [37.8141, 144.9633, 10],
            'sydney' => [33.8675, 151.2070, 10],
            'canberra' => [35.2820, 149.1287, 10],
            'hobart' => [42.8819, 147.3238, 10],
            'darwin' => [12.4628, 130.8418, 9.5],
            'adelaide' => [34.9286, 138.6000, 9.5],
            'perth' => [31.9535, 115.8570, 8]
        ];

    private static $uv_map =
        [
            'brisbane' => 'bri',
            'melbourne' => 'mel',
            'sydney' => 'syd',
            'canberra' => 'can',
            'darwin' => 'dar',
            'adelaide' => 'adl',
            'perth' => 'per'
        ];

    public static function realtime($selection = 'perth')
    {
        $json = file_get_contents(self::$realtime[$selection]);
        $weather = json_decode($json);

        $header = $weather->observations->header[0];
        $data = $weather->observations->data[0];

        return new Realtime($header->name, $header->state, $data->air_temp);
    }

    public static function forecast($selection = 'perth')
    {
        $xml = file_get_contents('ftp://ftp2.bom.gov.au/anon/gen/fwo/' . self::$forecast[$selection] . '.xml');
        $bom = new \SimpleXMLElement($xml);

        $forecast = [];

        foreach ($bom->forecast[0] as $area) {
            $index = 0;
            foreach ($area as $forecast_period) {
                if (++$index > 5) break;

                $start_time = (string)$forecast_period['start-time-local'];
                $dayOfWeek = Carbon::parse($start_time)->format('l');

                if ($area['aac'] == self::$forecast_subcodes[$selection][0] || $area['aac'] == self::$forecast_subcodes[$selection][1]) {
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
    public static function sunrise($selection = 'perth', $zenith = 90 + 50 / 60)
    {
        $latitude = self::$coordinates[$selection][0];
        $longitude = self::$coordinates[$selection][1];
        $gmt = self::$coordinates[$selection][2];
        $sunrise = date_sunrise(time(), SUNFUNCS_RET_STRING, $latitude, $longitude, $zenith, $gmt);
        $sunset = date_sunset(time(), SUNFUNCS_RET_STRING, $latitude, $longitude, $zenith, $gmt);
        return new Sunrise($latitude, $longitude, $zenith, $gmt, "Perth", $sunrise, $sunset);
    }

    /**
     * @return bool|UV
     */
    public static function uv_index($selection = 'perth')
    {
        $xml = file_get_contents("http://www.arpansa.gov.au/uvindex/realtime/xml/uvvalues.xml");
        $arpansa = new \SimpleXMLElement($xml);

        $code = self::$uv_map[$selection];

        foreach ($arpansa as $location) {
            if (strcmp($location->name, $code) === 0) {
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