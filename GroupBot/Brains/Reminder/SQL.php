<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 24/04/2016
 * Time: 9:26 PM
 */

namespace GroupBot\Brains\Reminder;


use GroupBot\Brains\Reminder\Types\Reminder;
use GroupBot\Database\DbConnection;

class SQL extends DbConnection
{
    public function select_reminders($user_id = NULL, $chat_id = NULL)
    {
        if (isset($user_id) && isset($chat_id)) {
            $sql = 'SELECT * FROM reminders WHERE chat_id = :chat_id AND user_id = :user_id';
        } elseif (isset($chat_id)) {
            $sql = 'SELECT * FROM reminders WHERE chat_id = :chat_id';
        } elseif (isset($user_id)) {
            $sql = 'SELECT * FROM reminders WHERE user_id = :user_id';
        } else {
            $sql = 'SELECT * FROM reminders';
        }
        
        $query = $this->db->prepare($sql);
        if (isset($chat_id)) $query->bindValue(':chat_id', $chat_id);
        if (isset($user_id)) $query->bindValue(':user_id', $user_id);

        $query->execute();

        if ($query->rowCount()) {
            return $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Brains\Reminder\Types\Reminder');
        }
        return false;
    }

    public function insert_reminder(Reminder $reminder)
    {
        $sql = 'INSERT INTO reminders (user_id, chat_id, date_due, date_created, content) VALUES (:user_id, :chat_id, :date_due, :date_created, :content)';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $reminder->chat_id);
        $query->bindValue(':user_id', $reminder->user_id);
        $query->bindValue(':date_due', $reminder->date_due);
        $query->bindValue(':date_created', $reminder->date_created);
        $query->bindValue(':content', $reminder->content);

        return $query->execute();
    }
    
    public function delete_reminder(Reminder $reminder)
    {
        $sql = 'DELETE FROM reminders WHERE id = :id';

        $query = $this->db->prepare($sql);
        $query->bindParam(':id', $reminder->id, \PDO::PARAM_INT);

        return $query->execute();
    }
}