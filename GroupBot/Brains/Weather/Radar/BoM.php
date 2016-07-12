<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 25/06/2016
 * Time: 6:11 PM
 */

namespace GroupBot\Brains\Weather\Radar;


class BoM
{
    public static function getBoMRadarString($radar_code, $image_radius_code)
    {
        return 'IDR' . $radar_code . $image_radius_code;
    }

    public static function getRadarTransparencies($radar_code, $image_radius_code)
    {
        $radar_string = self::getBoMRadarString($radar_code, $image_radius_code);

        $conn_id = ftp_connect("ftp2.bom.gov.au");
        ftp_login($conn_id, "anonymous", "guest");
        $contents = ftp_nlist($conn_id, '/anon/gen/radar/');
        $matches = preg_grep("/" . $radar_string . ".T/", $contents);

        $web_filenames = [];
        foreach ($matches as $match)
            $web_filenames[] = "ftp://ftp2.bom.gov.au/$match";

        ftp_close($conn_id);

        return $web_filenames;
    }
}