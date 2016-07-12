<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 25/06/2016
 * Time: 6:11 PM
 */

namespace GroupBot\Brains\Weather\Radar;


use GroupBot\Libraries\AnimGif;

class ImageProcessing
{
    public static function createBackground($radar_string)
    {
        if (file_exists("/var/www/html/bot/radar/" . $radar_string . "-template-simple.png")) return true;

        $base_url = "ftp://ftp2.bom.gov.au/anon/gen/radar_transparencies";
        $background = "$base_url/$radar_string.background.png";
        $locations = "$base_url/$radar_string.locations.png";
        $topography = "$base_url/$radar_string.topography.png";

        $background = imagecreatefrompng($background);
        $locations = imagecreatefrompng($locations);
        $topography = imagecreatefrompng($topography);

        imagecopymerge($background, $topography, 0, 0, 0, 0, imagesx($background), imagesy($background), 100);
        imagecopymerge($background, $locations, 0, 0, 0, 0, imagesx($background), imagesy($background), 100);

        imagepng($background, "/var/www/html/bot/radar/" . $radar_string . "-template-simple.png");

        imagedestroy($background);
        imagedestroy($locations);
        imagedestroy($topography);
        return true;
    }
    
    public static function overlay($image_paths, $radar_string)
    {
        $images = [];
        foreach ($image_paths as $filename) {
            $background = imagecreatefrompng("/var/www/html/bot/radar/" . $radar_string . "-template-simple.png");
            $radar = imagecreatefrompng($filename);
            imagecopymerge($background, $radar, 0, 0, 0, 0, imagesx($radar), imagesy($radar), 100);
            $images[] = $background;
        }
        return $images;
    }

    public static function animate($image_paths, $file_path)
    {
        $gc = new AnimGif();
        $gc->create($image_paths);
        $gc->save($file_path);
        return true;
    }
}