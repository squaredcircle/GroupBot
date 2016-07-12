<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 25/06/2016
 * Time: 6:09 PM
 */

namespace GroupBot\Brains\Weather\Radar;


class Radar
{
    /** @var  string */
    private $chat_id;

    /** @var \PDO */
    private $db;

    /** @var  Telegram */
    private $telegram;

    public function __construct($chat_id, \PDO $db)
    {
        $this->chat_id = $chat_id;
        $this->db = $db;
        $this->telegram = new Telegram($chat_id, $db);
    }

    private function levenshteinDistance($input, $words)
    {
        $shortest = -1;
        $closest = [];

        foreach ($words as $key => $word) {
            $lev = levenshtein($input, explode(' ', $word)[0]);

            if ($lev == 0 && count(explode(' ', $word)) == 1) {
                return $word;
            }

            if ($lev <= $shortest || $shortest < 0) {
                $closest[] = [$word, $lev, $key];
                $shortest = $lev;
            }
        }
        $top4 = array_slice(array_reverse($closest), 0, 3);
        foreach ($top4 as $key => $item) {
            if ($item[1] > 3)
                unset($top4[$key]);
        }

        if (count($top4) == 1) return reset($top4)[0];
        return count($top4) > 0 ?  $top4 : false;
    }

    public function getRadarCodeFromString($string)
    {
        if ($results = $this->levenshteinDistance($string, array_column(Radar_Codes::$radar_codes,1)))
        {
            if (!is_array($results)) {
                $key = array_search($results, array_column(Radar_Codes::$radar_codes, 1));
                return Radar_Codes::$radar_codes[$key][0];
            }
            return $results;
        }
        return false;
    }

    public function getImageRangeFromString($string)
    {
        $key = array_search($string, Radar_Codes::$image_ranges);
        return Radar_Codes::$image_ranges[$key][0];
    }

    public function createAndSendRadarGIF($radar_code, $image_radius_code)
    {
        $radar_string = BoM::getBoMRadarString($radar_code, $image_radius_code);
        $web_image_paths = BoM::getRadarTransparencies($radar_code, $image_radius_code);
        $file_path = self::getNewLocalFilePath($web_image_paths);

        if (count($web_image_paths) < 2) {
            \GroupBot\Telegram::talk($this->chat_id, emoji(0x274C) . " Something went wrong getting the radar images fam. The radar might be down. Check here to see if it's working: \n \nhttp://www.bom.gov.au/products/" . $radar_string . ".loop.shtml#skip");
            return false;
        }

        if (!$this->telegram->sendIfExists($file_path)) {
            \GroupBot\Telegram::sendChatSendingPhotoStatus($this->chat_id);

            ImageProcessing::createBackground($radar_string);
            $images = ImageProcessing::overlay($web_image_paths, $radar_string);
            ImageProcessing::animate($images, $file_path);

            $this->telegram->sendGIFThroughTelegram($file_path);
        }
        return true;
    }

    private static function getNewLocalFilePath($web_image_paths)
    {
        $last_filename = end($web_image_paths);
        $info = pathinfo($last_filename);
        $title = $info['filename'] . '.gif';
        return "/var/www/html/bot/radar/$title";
    }
}
