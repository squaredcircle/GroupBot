<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:12 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\PhotoCache;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class spoonm extends Command
{
    public function main()
    {
        Telegram::sendChatSendingPhotoStatus($this->Message->Chat->id);
        PhotoCache::SendPhotoByPath($this->db, PHOTO_DIR . '/spoonm.jpg', $this->Message->Chat->id);
    }
}