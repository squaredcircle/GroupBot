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

    /** @var  boolean */
    public $bot_kick_mode;

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

    public function construct($id, ChatType $type, $title, $messages_sent_last_min, $admin_user_id = NULL, $banker_name = "The Bank", $currency_name = "Coin", $welcome_enabled = true, $no_spam_mode = false, $yandex_api_key = NULL, $yandex_enabled = true, $yandex_language = "English", $yandex_min_words = 4, $bot_kick_mode = false)
    {
        $this->id = $id;
        $this->type = $type;
        $this->title = $title;
        $this->messages_sent_last_min = $messages_sent_last_min;
        $this->admin_user_id =$admin_user_id;
        $this->banker_name = $banker_name;
        $this->currency_name = $currency_name;
        $this->welcome_enabled = $welcome_enabled;
        $this->no_spam_mode = $no_spam_mode;
        $this->yandex_api_key = $yandex_api_key;
        $this->yandex_enabled = $yandex_enabled;
        $this->yandex_language = $yandex_language;
        $this->yandex_min_words = $yandex_min_words;
        $this->bot_kick_mode = $bot_kick_mode;
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
    private function determineChatType($chat_type): bool|ChatType
    {
        switch ($chat_type['type']) {
            case 'private':
                return ChatType::Individual;
                break;
            case 'group':
                return ChatType::Group;
                break;
            case 'supergroup':
                return ChatType::SuperGroup;
                break;
            case 'channel':
                return ChatType::Channel;
                break;
        }
        return false;
    }

    public function isPrivate()
    {
        return $this->type == ChatType::Individual;
    }
}