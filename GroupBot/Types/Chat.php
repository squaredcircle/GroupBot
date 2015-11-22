<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 15/11/2015
 * Time: 6:46 PM
 */

namespace GroupBot\Types;

use GroupBot\Enums\ChatType;

class Chat
{
    public $id;
    public $type;
    public $title;
    public $user_name;
    public $first_name;
    public $last_name;

    public function __construct($chat)
    {
        $this->id = $chat['id'];
        $this->type = $this->determineChatType($chat);
        $this->title = isset($chat['title']) ? $chat['title'] : NULL;
        $this->user_name = isset($chat['username']) ? $chat['username'] : NULL;
        $this->first_name = isset($chat['first_name']) ? $chat['first_name'] : NULL;
        $this->last_name = isset($chat['last_name']) ? $chat['last_name'] : NULL;
    }

    private function determineChatType($chat)
    {
        if (isset($chat['type'])) {
            switch ($chat['type']) {
                case 'private':
                    return new ChatType(ChatType::Individual);
                    break;
                case 'group':
                    return new ChatType(ChatType::Group);
                    break;
                case 'channel':
                    return new ChatType(ChatType::Channel);
                    break;
            }
        }

        return NULL;
    }

    public function hasUserName()
    {
        return isset($this->user_name);
    }

    public function hasFirstName()
    {
        return isset($this->first_name);
    }

    public function hasLastName()
    {
        return isset($this->last_name);
    }

    public function hasFullName()
    {
        return ($this->hasFirstName() && $this->hasLastName());
    }
}