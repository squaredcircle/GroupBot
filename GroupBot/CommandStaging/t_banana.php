<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:12 AM
 */
namespace GroupBot\Command;


use GroupBot\Brains\PhotoCache;
use GroupBot\Types\Command;

class t_banana extends Command
{
    public function t_banana()
    {
        $local_path = random_pic(BANANA_DIR);
        PhotoCache::SendPhotoByPath($this->db, $local_path, $this->Message->Chat->id);
    }
}