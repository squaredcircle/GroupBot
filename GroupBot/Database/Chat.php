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
        $sql = "INSERT INTO chats (chat_id, type, title, messages_sent_last_min) VALUES (:chat_id, :type, :title, :messages_sent_last_min)
                ON DUPLICATE KEY UPDATE type = VALUES(type), title = VALUES(title), messages_sent_last_min = VALUES(messages_sent_last_min)";

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $chat->id);
        $query->bindValue(':type', $chat->type);
        $query->bindValue(':title', $chat->title);
        $query->bindValue(':messages_sent_last_min', $chat->messages_sent_last_min);
        return $query->execute();
    }

    /**
     * @param $chat_id
     * @return bool|\GroupBot\Types\Chat
     */
    public function getChatById($chat_id)
    {
        $sql = 'SELECT chat_id, type, title, messages_sent_last_min, admin_user_id, banker_name, currency_name, yandex_api_key, yandex_enabled, yandex_language, yandex_min_words FROM chats WHERE chat_id = :chat_id';

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
}