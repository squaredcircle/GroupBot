<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:12 AM
 */
namespace GroupBot\Command;

use GroupBot\Types\Command;
use GroupBot\Base\DbControl;

class t_photo extends Command
{
    public function t_photo()
    {
        $local_path = random_pic(PHOTO_DIR);

        $db = new DbControl();

        $file_id = $db->getServerPhotoId($local_path);

        if ($file_id !== false) {
            $this->Telegram->fileIdPhotoSender($this->Message->Chat->id, $file_id);
        } else {
            $back = $this->Telegram->customPhotoSender2($this->Message->Chat->id, $local_path);

            $back = json_decode($back, true);

            $file_id = end($back['result']['photo'])['file_id'];

            $db->addServerPhotoId($file_id, $local_path);
        }
    }
}