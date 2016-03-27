<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:15 AM
 */
namespace GroupBot\Command;

use GroupBot\Telegram;
use GroupBot\Types\Command;
use Imagick;
use DateTime;

class t_wolfram extends Command
{
    public function t_wolfram()
    {
        $wolf = file_get_contents('http://www.wolframalpha.com/input/?i=' . urlencode($this->Message->text));

        preg_match_all("/http:\/\/www.*.wolframalpha.com\/Calculate\/[^<>]*\./", $wolf, $matches, PREG_SET_ORDER);

        $img = new Imagick();
        foreach ($matches as $i)
        {
            $handle =  fopen($i[0], 'rb');
            $img->readImageFile($handle);
        }

        $img->resetIterator();
        $combined =  $img->appendImages(true);

        $date = new DateTime();
        $path = WOLFRAM_DIR . '/img' . $date->getTimestamp() . '.gif';
        $combined->writeImage($path);
        Telegram::customPhotoSender($this->Message->Chat->id, $path);
    }
}