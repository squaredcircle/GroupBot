<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:07 AM
 */
namespace GroupBot\Command;


use GroupBot\Database\Photo;
use GroupBot\Libraries\AnimGif;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class radar extends Command
{
    private $choices = [
        'perth' => [
            '64km' => 'IDR704',
            '128km' => 'IDR703',
            '256km' => 'IDR702',
            '512km' => 'IDR701'
        ]
    ];

    private $choice = 'IDR703';
    private $title, $filename;

    private function get_images()
    {
        $conn_id = ftp_connect("ftp2.bom.gov.au");
        ftp_login($conn_id, "anonymous", "guest");
        $contents = ftp_nlist($conn_id, '/anon/gen/radar/');
        $matches = preg_grep("/" . $this->choice . ".T/", $contents);

        $web_filenames = [];
        foreach ($matches as $match)
            $web_filenames[] = "ftp://ftp2.bom.gov.au/$match";

        ftp_close($conn_id);

        return $web_filenames;
    }

    private function overlay($filenames)
    {
        $images = [];
        foreach ($filenames as $filename) {
            $background = imagecreatefrompng("/var/www/html/bot/radar/" . $this->choice . "-template-simple.png");
            $radar = imagecreatefrompng($filename);
            imagecopymerge($background, $radar, 0, 0, 0, 0, imagesx($radar), imagesy($radar), 100);
            $images[] = $background;
        }
        return $images;
    }

    private function animate($images)
    {
        $gc = new AnimGif();
        $gc->create($images);
        $gc->save($this->filename);
        return true;
    }

    private function sendIfExists()
    {
        $photoSQL = new Photo($this->db);
        $file_id = $photoSQL->getRadarPhotoId($this->filename);
        if (!$file_id) return false;

        Telegram::fileIdDocumentSender($this->Message->Chat->id, $file_id);
        return true;
    }

    private function sendGIFThroughTelegram()
    {
        $back = Telegram::sendDocument($this->Message->Chat->id, $this->filename);
        $back = json_decode($back, true);
        $file_id = $back['result']['document']['file_id'];

        $photoSQL = new Photo($this->db);
        $photoSQL->addServerPhotoId($file_id, '', $this->filename);
    }
    

    private function setTitle($filenames)
    {
        $last_filename = end($filenames);
        $info = pathinfo($last_filename);
        $this->title = $info['filename'] . '.gif';
        $this->filename =  '/var/www/html/bot/radar/' . $this->title;
    }

    private function sendMovingGIF()
    {
        Telegram::sendChatSendingPhotoStatus($this->Message->Chat->id);
        $filenames = $this->get_images();
        $this->setTitle($filenames);

        if (!$this->sendIfExists()) {
            Telegram::sendChatSendingPhotoStatus($this->Message->Chat->id);
            $images = $this->overlay($filenames);
            $this->animate($images);
            $this->sendGIFThroughTelegram();
        }

        return true;
    }

    private function getBasicImage()
    {
        $url = 'ftp://ftp2.bom.gov.au/anon/gen/radar/' . $this->choice . '.gif';
        $img = '/var/www/html/bot/radar/' . time() . '.gif';
        file_put_contents($img, file_get_contents($url));
        return $img;
    }

    public function main()
    {
        if ($this->isParam()) {
            $city = $this->getParam();
            if ($this->noParams() > 1) {
                $range = $this->getParam(1);
            }

            if (array_key_exists($city, $this->choices)) {
                if (isset($range)) {
                    if (array_key_exists($range, $this->choices[$city])) {
                        $this->choice = $this->choices[$city][$range];
                    } else {
                        Telegram::talk($this->Message->Chat->id, "can't find that range, fam\nusually there's `128km`, `256km` or `512km` available");
                        return false;
                    }
                } else {
                    $this->choice = reset($this->choices[$city]);
                }
            } else {
                Telegram::talk($this->Message->Chat->id, "can't find that city, fam\ntry using lower case");
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
        $this->sendMovingGIF();

        // Release lock
        fclose($fp);
        return true;
    }
}