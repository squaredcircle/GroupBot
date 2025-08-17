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
    public int $id;
    public int|null $chat_id;
    public ChatType $type;
    public string|null $title;
    public int $messages_sent_last_min;
    public int|null $admin_user_id;
    public string $banker_name;
    public string $currency_name;
    public bool $welcome_enabled;
    public bool $no_spam_mode;
    public string|null $yandex_api_key;
    public bool $yandex_enabled;
    public string $yandex_language;
    public int $yandex_min_words;
    public bool $bot_kick_mode;


    public static function constructFromTelegramUpdate($chat_update, \PDO $db): Chat|bool
    {
        $changed = false;

        $chatSQL = new \GroupBot\Database\Chat($db);
        if ($chat = $chatSQL->getChatById($chat_update['id'])) {
            if (isset($chat_update['title']) && strcmp($chat->title, $chat_update['title']) !== 0) {
                $chat->title = $chat_update['title'];
                $changed = true;
            }
            $chat->id = $chat->chat_id ?? $chat_update['id'];
            unset($chat->chat_id);
        } else {
            $chat = new Chat();
            $chat->construct(
                $chat_update['id'],
                $chat->determineChatType($chat_update),
                $chat_update['title'] ?? NULL,
                0
            );
            $changed = true;
        }
        
        if ($changed) $chat->save($db);
        return $chat;
    }

    public function save(\PDO $db): bool
    {
        $userSQL = new \GroupBot\Database\Chat($db);
        return $userSQL->updateChat($this);
    }

    public function construct(
        int $id,
        int $type,
        string $title,
        int $messages_sent_last_min,
        int|null $chat_id = NULL,
        int|null $admin_user_id = NULL,
        string $banker_name = "The Bank",
        string $currency_name = "Coin",
        bool $no_spam_mode = false,
        bool $welcome_enabled = true,
        string|null $yandex_api_key = NULL,
        bool $yandex_enabled = true,
        string $yandex_language = "en",
        int $yandex_min_words = 4,
        bool $bot_kick_mode = false
        ): void
    {
        $this->id = $id;
        $this->chat_id = $chat_id;
        $this->type = ChatType::from($type);
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

    public function waitBeforeSend(): int
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
     * @param $chat_type
     * @return bool|ChatType
     */
    private function determineChatType($chat_type): bool|ChatType
    {
        return match ($chat_type['type']) {
            'private' => ChatType::Individual,
            'group' => ChatType::Group,
            'supergroup' => ChatType::SuperGroup,
            'channel' => ChatType::Channel,
            default => false,
        };
    }

    public function isPrivate()
    {
        return $this->type == ChatType::Individual;
    }
}