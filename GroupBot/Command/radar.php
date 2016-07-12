<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:07 AM
 */
namespace GroupBot\Command;


use GroupBot\Brains\Weather\Radar\Radar_Codes;
use GroupBot\Database\Photo;
use GroupBot\Libraries\AnimGif;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class radar extends Command
{
    private function getBasicImage($radar_string)
    {
        $url = 'ftp://ftp2.bom.gov.au/anon/gen/radar/' . $radar_string . '.gif';
        $img = '/var/www/html/bot/radar/' . time() . '.gif';
        file_put_contents($img, file_get_contents($url));
        return $img;
    }

    public function main()
    {
        return true;
        //Telegram::sendChatSendingPhotoStatus($this->Message->Chat->id);

        $Radar = new \GroupBot\Brains\Weather\Radar\Radar($this->Message->Chat->id, $this->db);

        $radar_code = 70;
        $image_radius_code = 3;

        if ($this->Message->isCallback())
        {
            $key = $this->getParam();
            $radar_code = Radar_Codes::$radar_codes[$key][0];
            if (!$radar_code) {
                Telegram::edit_inline_message($this->Message->Chat->id, $this->Message->message_id, "Something went wrong!", []);
                return false;
            }
            Telegram::edit_inline_message($this->Message->Chat->id, $this->Message->message_id, "Showing $radar_code", []);
        }
        elseif ($this->isParam())
        {
            if (!($radar_code = $Radar->getRadarCodeFromString($this->getParam()))) {
                Telegram::talk($this->Message->Chat->id, "Can't find that location, fam");
                return false;
            }
            if (is_array($radar_code)) {
                $keyboard = [];
                $row = [];
                foreach ($radar_code as $name) {
                    $row[] = [
                        'text' => $name[0] . " (" . $name[2] . ")",
                        'callback_data' => "/radar $name[2]"
                    ];
                }
                $keyboard[] = $row;
                Telegram::talk_inline_keyboard($this->Message->Chat->id, "Did you mean one of these?", $keyboard);
                return false;
            }
            if ($this->noParams() > 1 && !($image_radius_code = $Radar->getImageRangeFromString($this->getParam(1)))) {
                Telegram::talk($this->Message->Chat->id, "can't find that range, fam\nusually there's `128km`, `256km` or `512km` available");
                return false;
            }
        }

        // Acquire lock
        $fp = fopen('/var/www/html/bot/radar/lock', 'r+');
        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            Telegram::talk($this->Message->Chat->id, "cool it, m8\n" . emoji(0x1F914) . " i'm thinking");
            return false;
        }

        // Payload
        $Radar->createAndSendRadarGIF($radar_code, $image_radius_code);

        // Release lock
        fclose($fp);
        return true;
    }
}