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
    /** @var  integer */
    public $id;

    /** @var  ChatType */
    public $type;

    /** @var  string */
    public $title;

    /** @var  integer */
    public $messages_sent_last_min;

    /** @var  integer */
    public $admin_user_id;

    /** @var  string */
    public $banker_name;

    /** @var  string */
    public $currency_name;

    /** @var  boolean */
    public $welcome_enabled;

    /** @var  boolean */
    public $no_spam_mode;

    /** @var  string */
    public $yandex_api_key;

    /** @var  boolean */
    public $yandex_enabled;

    /** @var  string */
    public $yandex_language;

    /** @var  integer */
    public $yandex_min_words;

    public static function constructFromTelegramUpdate($chat_update, \PDO $db)
    {
        $changed = false;

        $chatSQL = new \GroupBot\Database\Chat($db);
        if ($chat = $chatSQL->getChatById($chat_update['id'])) {
            if (isset($chat_update['title']) && strcmp($chat->title, $chat_update['title']) !== 0) {
                $chat->title = $chat_update['title'];
                $changed = true;
            }
            $chat->id = $chat->chat_id;
            unset($chat->chat_id);
        } else {
            $chat = new Chat();
            $chat->construct(
                $chat_update['id'],
                $chat->determineChatType($chat_update),
                isset($chat_update['title']) ? $chat_update['title'] : NULL,
                0
            );
            $changed = true;
        }
        
        if ($changed) $chat->save($db);
        return $chat;
    }

    public function save(\PDO $db)
    {
        $userSQL = new \GroupBot\Database\Chat($db);
        return $userSQL->updateChat($this);
    }

    public function construct($id, ChatType $type, $title, $messages_sent_last_min)
    {
        $this->id = $id;
        $this->type = $type;
        $this->title = $title;
        $this->messages_sent_last_min = $messages_sent_last_min;
    }

    public function waitBeforeSend()
    {
        if ($this->type == ChatType::Group || $this->type == ChatType::SuperGroup) {
            if ($this->messages_sent_last_min <= 10) return 0;
            if ($this->messages_sent_last_min <= 14) return 1;
            if ($this->messages_sent_last_min <= 17) return 2;
            if ($this->messages_sent_last_min <= 20) return 3;
        }
        if ($this->type == ChatType::Individual || $this->type == ChatType::Channel) {
            if ($this->messages_sent_last_min <= 30) return 0;
            if ($this->messages_sent_last_min <= 40) return 1;
            if ($this->messages_sent_last_min <= 50) return 2;
            if ($this->messages_sent_last_min <= 60) return 3;
        }
        return 0;
    }

    /**
     * @param string $chat_type
     * @return bool|ChatType
     */
    private function determineChatType($chat_type)
    {
        switch ($chat_type['type']) {
            case 'private':
                return new ChatType(ChatType::Individual);
                break;
            case 'group':
                return new ChatType(ChatType::Group);
                break;
            case 'supergroup':
                return new ChatType(ChatType::SuperGroup);
                break;
            case 'channel':
                return new ChatType(ChatType::Channel);
                break;
        }
        return false;
    }

    public function isPrivate()
    {
        return $this->type == ChatType::Individual;
    }
}