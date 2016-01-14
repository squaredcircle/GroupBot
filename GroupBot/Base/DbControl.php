<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:23 PM
 */

namespace GroupBot\Base;


use GroupBot\Types\User;

class DbControl
{
    private $db;

    public function __construct()
    {
        try {
            $this->db = new \PDO('mysql:host=' . BOT_DB_HOST . ';dbname=' . BOT_DB_NAME . ';charset=utf8', BOT_DB_USER, BOT_DB_PASSWORD);
            return true;
        } catch (\PDOException $e) {
            echo "PDO database connection problem: " . $e->getMessage();
        } catch (\Exception $e) {
            echo "General problem: " . $e->getMessage();
        }
        return false;
    }

    public function getObject()
    {
        return $this->db;
    }

    public function addServerPhotoId($file_id, $local_path)
    {
        $sql = 'INSERT INTO photos (file_id, local_path)
                VALUES (:file_id, :local_path)';

        $query = $this->db->prepare($sql);
        $query->bindValue(':file_id', $file_id);
        $query->bindValue(':local_path', $local_path);

        return $query->execute();
    }

    public function getServerPhotoId($local_path)
    {
        $sql = 'SELECT file_id, local_path
                FROM photos
                WHERE local_path = :local_path';

        $query = $this->db->prepare($sql);
        $query->bindValue(':local_path', $local_path);
        $query->execute();

        if ($query->rowCount()) {
            return $query->fetch()['file_id'];
        } else {
            return false;
        }
    }

    public function resetDailyCounters()
    {
        $sql1 = 'UPDATE stats
                SET posts_today = 0';
        $sql2 = 'UPDATE stats_commands
                SET uses_today = 0';
        $sql3 = 'TRUNCATE bj_stats_today';
        $query1 = $this->db->prepare($sql1);
        $query2 = $this->db->prepare($sql2);
        $query3 = $this->db->prepare($sql3);
        return $query1->execute() && $query2->execute() && $query3->execute();
    }

    public function isUserLogged($user_str, $chat_id)
    {
        $sql = 'SELECT user_id
                FROM stats
                WHERE chat_id = :chat_id AND
                (user_firstname = :user_str OR user_secondname = :user_str OR username = :user_str)';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $chat_id);
        $query->bindValue(':user_str', $user_str);
        $query->execute();

        if ($query->rowCount()) {
            return $query->fetch()['user_id'];
        } else {
            return false;
        }
    }

    public function getUserCommandLogsInChat($chat_id, $user_id)
    {
        $sql = 'SELECT command, uses, uses_today, last_used
                FROM stats_commands
                WHERE chat_id = :chat_id AND user_id = :user_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $chat_id);
        $query->bindValue(':user_id', $user_id);
        $query->execute();

        if ($query->rowCount()) {
            return $query->fetchAll();
        } else {
            return false;
        }
    }

    public function getUserPostLogsInChat($chat_id, $user_id)
    {
        $sql = 'SELECT user_id, user_firstname, user_secondname, username, posts, posts_today, lastpost, lastpost_date
                FROM stats
                WHERE chat_id = :chat_id AND user_id = :user_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $chat_id);
        $query->bindValue(':user_id', $user_id);
        $query->execute();

        if ($query->rowCount()) {
            return $query->fetch();
        } else {
            return false;
        }
    }

    public function updatePostLogs($chat_id, User $User, $lastpost)
    {
        $sql = 'INSERT INTO stats (chat_id, user_id, user_firstname, user_secondname, username, posts, posts_today, lastpost, lastpost_date)
                VALUES (:chat_id, :user_id, :user_firstname, :user_secondname, :username, 1, 1, :lastpost, NOW())
                ON DUPLICATE KEY UPDATE
                chat_id = VALUES(chat_id), user_firstname = VALUES(user_firstname), user_secondname = VALUES(user_secondname),
                username = VALUES(username), posts = posts + 1, posts_today = posts_today + 1, lastpost = VALUES(lastpost),
                lastpost_date = VALUES(lastpost_date)';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $chat_id);
        $query->bindValue(':user_id', $User->id);
        $query->bindValue(':user_firstname', $User->first_name);
        $query->bindValue(':user_secondname', isset($User->last_name) ? $User->last_name : '');
        $query->bindValue(':username', isset($User->user_name) ? $User->user_name : '');
        $query->bindValue(':lastpost', isset($lastpost) ? $lastpost : '');
        return $query->execute();
    }

    public function updateCommandLogs($command, $chat_id, $user_id)
    {
        $sql = 'INSERT INTO stats_commands (command, chat_id, user_id, uses, uses_today, last_used)
                VALUES (:command, :chat_id, :user_id, 1, 1, NOW())
                ON DUPLICATE KEY UPDATE
                command = VALUES(command), user_id =  VALUES(user_id), uses = uses + 1, uses_today = uses_today + 1, last_used = NOW()';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $chat_id);
        $query->bindValue(':user_id', $user_id);
        $query->bindValue(':command', $command);
        return $query->execute();
    }
}