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

class radar extends Command
{
    public function main()
    {
        $url = 'ftp://ftp2.bom.gov.au/anon/gen/radar/IDR702.gif';
        $img = '/var/www/html/bot/radar/' . time() . '.gif';
        file_put_contents($img, file_get_contents($url));

        Telegram::sendDocument($this->Message->Chat->id, $img);
    }
}