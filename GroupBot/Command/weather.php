<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:07 AM
 */
namespace GroupBot\Command;

use GroupBot\Telegram;
use GroupBot\Types\Command;

class weather extends Command
{
    private function uv_index()
    {
        $xml = file_get_contents("http://www.arpansa.gov.au/uvindex/realtime/xml/uvvalues.xml");
        $arpansa = new \SimpleXMLElement($xml);

        foreach ($arpansa as $location) {
            if (strcmp($location->name, 'per') ===0) return $location->index;
        }

        return false;
    }

    private function uv_code($index)
    {
        if ($index <= 2.5) return ["green", "low"];
        if ($index <= 5.5) return ["yellow", "moderate"];
        if ($index <= 7.5) return ["orange", "high"];
        if ($index <= 10.5) return ["red", "very high"];
        return ["purple", "extreme"];
    }

    private function today()
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

    function sun()
    {
        $lat = -31.9535;
        $long = 115.8570;
        $zenith = 90 + 50 / 60;
        $gmt = 8;

        $sunrise = date_sunrise(time(), SUNFUNCS_RET_STRING, $lat, $long, $zenith, $gmt);
        $sunset = date_sunset(time(), SUNFUNCS_RET_STRING, $lat, $long, $zenith, $gmt);
        return [$sunrise, $sunset];
    }

    public function main()
    {
        $json = file_get_contents('http://www.bom.gov.au/fwo/IDW60901/IDW60901.94608.json');
        $weather = json_decode($json);

        $header = $weather->observations->header[0];
        $now = $weather->observations->data[0];

        $today = $this->today();

        $index = $this->uv_index();

        $sun = $this->sun();

        $out = emoji(0x26C5) . " Weather for *" . $header->name . "*, *" . $header->state . "*."
            . "\n"
            . "\n`   `• Currently *" . $now->air_temp . "°C*"
            . "\n`   `• " . $today[1] . " Max *" . $today[0] . "°C* today"
            . "\n`   `• The UV index is at *" . $index . "* ([" . $this->uv_code($index)[1] . "](http://www.arpansa.gov.au/uvindex/realtime/images//per_rt.gif))"
            . "\n`   `• Sunrise is at *$sun[0]* today, and sunset at *$sun[1]*";


        Telegram::talk($this->Message->Chat->id, $out, true);
    }
}