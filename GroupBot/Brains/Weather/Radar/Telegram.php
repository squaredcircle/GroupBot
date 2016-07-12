<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 25/06/2016
 * Time: 6:58 PM
 */

namespace GroupBot\Brains\Weather\Radar;


use GroupBot\Database\Photo;

class Telegram
{
    /** @var  string */
    private $chat_id;

    /** @var \PDO  */
    private $db;

    public function __construct($chat_id, \PDO $db)
    {
        $this->chat_id = $chat_id;
        $this->db = $db;
    }

    public function sendIfExists($file_path)
    {
        $photoSQL = new Photo($this->db);
        $file_id = $photoSQL->getRadarPhotoId($file_path);
        if (!$file_id) return false;

        \GroupBot\Telegram::fileIdDocumentSender($this->chat_id, $file_id);
        return true;
    }

    public function sendGIFThroughTelegram($file_path)
    {
        $back = \GroupBot\Telegram::sendDocument($this->chat_id, $file_path);
        $back = json_decode($back, true);
        $file_id = $back['result']['document']['file_id'];

        $photoSQL = new Photo($this->db);
        $photoSQL->addServerPhotoId($file_id, '', $file_path);
    }
}