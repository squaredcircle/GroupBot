<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:07 AM
 */
namespace GroupBot\Command;


use GroupBot\Libraries\AnimGif;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class radar extends Command
{
    private function get_images()
    {
        $conn_id = ftp_connect("ftp2.bom.gov.au");
        ftp_login($conn_id, "anonymous", "guest");
        $contents = ftp_nlist($conn_id, '/anon/gen/radar/');
        $matches = preg_grep("/IDR702.T/", $contents);

        $web_filenames = [];
        foreach ($matches as $match) $web_filenames[] = "ftp://ftp2.bom.gov.au/$match";

        ftp_close($conn_id);

        return $web_filenames;
    }

    private function overlay($filenames)
    {
        $images = [];
        foreach ($filenames as $filename) {
            $background = imagecreatefrompng("/var/www/html/bot/radar/IDR702-template.png");
            $radar = imagecreatefrompng($filename);
            imagecopymerge($background, $radar, 0, 0, 0, 0, imagesx($radar), imagesy($radar), 100);
            $images[] = $background;
        }
        return $images;
    }

    private function animate($images)
    {
        $filename = '/var/www/html/bot/radar/' . time() . '.gif';
        $gc = new AnimGif();
        $gc->create($images);
        $gc->save($filename);
        return $filename;
    }

    private function getMovingGIF()
    {
        $filenames = $this->get_images();
        $images = $this->overlay($filenames);
        $filename = $this->animate($images);

        return $filename;
    }

    private function getBasicImage()
    {
        $url = 'ftp://ftp2.bom.gov.au/anon/gen/radar/IDR702.gif';
        $img = '/var/www/html/bot/radar/' . time() . '.gif';
        file_put_contents($img, file_get_contents($url));
        return $img;
    }

    public function main()
    {
        // Aquire lock
        $fp = fopen('/var/www/html/bot/radar/lock', 'r+');
        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            Telegram::talk($this->Message->Chat->id, "cool it, m8\n" . emoji(0x1F914) . " i'm thinking");
            return false;
        }

        // Payload
        $filename = $this->getMovingGIF();
        Telegram::sendDocument($this->Message->Chat->id, $filename);

        // Release lock
        fclose($fp);
        return true;
    }
}