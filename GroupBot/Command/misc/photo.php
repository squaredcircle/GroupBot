<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:12 AM
 */
namespace GroupBot\Command\misc;

use GroupBot\Brains\PhotoCache;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class photo extends Command
{
    public function main()
    {
        Telegram::sendChatSendingPhotoStatus($this->Message->Chat->id);
        $local_path = random_pic(PHOTO_DIR);
        PhotoCache::SendPhotoByPath($this->db, $local_path, $this->Message->Chat->id);
    }
}