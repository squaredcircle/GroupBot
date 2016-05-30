<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 23/02/2016
 * Time: 1:01 AM
 */

namespace GroupBot\Database;


class Chat extends DbConnection
{
    /**
     * @param \GroupBot\Types\Chat $chat
     * @return bool
     */
    public function updateChat(\GroupBot\Types\Chat $chat)
    {
        $sql = "INSERT INTO chats 
                  (chat_id, type, title, messages_sent_last_min, admin_user_id, banker_name, currency_name, welcome_enabled, no_spam_mode, yandex_api_key, yandex_enabled, yandex_language, yandex_min_words, bot_kick_mode) 
                VALUES
                  (:chat_id, :type, :title, :messages_sent_last_min, :admin_user_id, :banker_name, :currency_name, :welcome_enabled, :no_spam_mode, :yandex_api_key, :yandex_enabled, :yandex_language, :yandex_min_words, :bot_kick_mode)
                ON DUPLICATE KEY UPDATE 
                  type = VALUES(type),
                  title = VALUES(title),
                  messages_sent_last_min = VALUES(messages_sent_last_min), 
                  admin_user_id = VALUES(admin_user_id ),
                  banker_name = VALUES(banker_name),
                  currency_name = VALUES(currency_name),
                  welcome_enabled = VALUES(welcome_enabled),
                  no_spam_mode = VALUES(no_spam_mode),
                  yandex_api_key = VALUES(yandex_api_key),
                  yandex_enabled = VALUES(yandex_enabled), 
                  yandex_language = VALUES(yandex_language), 
                  yandex_min_words = VALUES(yandex_min_words),
                  bot_kick_mode = VALUES(bot_kick_mode)";

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $chat->id);
        $query->bindValue(':type', $chat->type);
        $query->bindValue(':title', $chat->title);
        $query->bindValue(':messages_sent_last_min', $chat->messages_sent_last_min);
        $query->bindValue(':admin_user_id', $chat->admin_user_id);
        $query->bindValue(':banker_name', $chat->banker_name);
        $query->bindValue(':currency_name', $chat->currency_name);
        $query->bindValue(':welcome_enabled', $chat->welcome_enabled);
        $query->bindValue(':no_spam_mode', $chat->no_spam_mode);
        $query->bindValue(':yandex_api_key', $chat->yandex_api_key);
        $query->bindValue(':yandex_enabled', $chat->yandex_enabled);
        $query->bindValue(':yandex_language', $chat->yandex_language);
        $query->bindValue(':yandex_min_words', $chat->yandex_min_words);
        $query->bindValue(':bot_kick_mode', $chat->bot_kick_mode);
        return $query->execute();
    }

    /**
     * @param $chat_id
     * @return bool|\GroupBot\Types\Chat
     */
    public function getChatById($chat_id)
    {
        $sql = 'SELECT * FROM chats WHERE chat_id = :chat_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $chat_id);
        $query->execute();

        if ($query->rowCount()) {
            $query->setFetchMode(\PDO::FETCH_CLASS, 'GroupBot\Types\Chat');
            if ($chat = $query->fetch()) {
                $chat->id = $chat->chat_id;
                return $chat;
            }
        }
        return false;
    }

    public function getChatsByAdmin($admin_user_id)
    {
        $sql = 'SELECT * FROM chats WHERE admin_user_id = :admin_user_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':admin_user_id', $admin_user_id);
        $query->execute();

        if ($query->rowCount()) {
            if ($chats = $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Types\Chat')) {
                foreach ($chats as $chat) {
                    $chat->id = $chat->chat_id;
                }
            }
            return $chats;
        }
        return false;
    }

    public function getNoChats()
    {
        $sql = 'SELECT chat_id FROM chats';

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->rowCount();
    }
}