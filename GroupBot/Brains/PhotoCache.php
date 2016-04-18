<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 20/01/2016
 * Time: 1:19 PM
 */

namespace GroupBot\Brains;


use GroupBot\Database\Photo;
use GroupBot\Telegram;

class PhotoCache
{
    public static function SendPhotoByPath(\PDO $db, $local_path, $chat_id)
    {
        $md5 = md5_file($local_path, true);

        if (isset($local_path)) {
            $photoSQL = new Photo($db);

            $file_id = $photoSQL->getServerPhotoId($md5, $local_path);

            if ($file_id !== false) {
                Telegram::fileIdPhotoSender($chat_id, $file_id);
            } else {
                $back = Telegram::customPhotoSender($chat_id, $local_path);

                $back = json_decode($back, true);

                $file_id = end($back['result']['photo'])['file_id'];

                $photoSQL->addServerPhotoId($file_id, $md5, $local_path);
            }
        }
    }

    public static function SendDocumentByPath(\PDO $db, $local_path, $chat_id)
    {
        $md5 = md5_file($local_path, true);

        if (isset($local_path)) {
            $photoSQL = new Photo($db);

            $file_id = $photoSQL->getServerPhotoId($md5, $local_path);

            if ($file_id !== false) {
                Telegram::fileIdPhotoSender($chat_id, $file_id);
            } else {
                $back = Telegram::sendDocument($chat_id, $local_path);

                $back = json_decode($back, true);

                $file_id = end($back['result']['photo'])['file_id'];

                $photoSQL->addServerPhotoId($file_id, $md5, $local_path);
            }
        }
    }
}