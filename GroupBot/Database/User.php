<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 22/02/2016
 * Time: 7:30 PM
 */

namespace GroupBot\Database;


use GroupBot\Types\Chat;
use GroupBot\Types\Message;
use GroupBot\Types\UserPostStats;

class User extends DbConnection
{
    private function removeDuplicateUsername($user_id, $user_name)
    {
        $user = $this->getUserFromUserName($user_name);
        if (!$user) return false;
        if ($user->user_id == $user_id) return false;

        $sql = 'UPDATE users SET user_name = null WHERE user_name = :user_name';
        $query = $this->db->prepare($sql);
        $query->bindValue(':user_name', $user->user_name);
        return $query->execute();
    }

    /**
     * @param \GroupBot\Types\User $user
     * @return bool
     */
    public function updateUser(\GroupBot\Types\User $user)
    {
        $this->removeDuplicateUsername($user->user_id, $user->user_name);

        $sql = "
            INSERT INTO users
              (user_id, first_name, user_name, last_name, balance, level, last_activity, received_income_today, free_bets_today, handle_preference, welcome_sent, timezone, location)
            VALUES
              (:user_id, :first_name, :user_name, :last_name, :balance, :level, :last_activity, :received_income_today, :free_bets_today, :handle_preference, :welcome_sent, :timezone, :location)
              ON DUPLICATE KEY UPDATE
              first_name = VALUES(first_name),
              user_name = VALUES(user_name),
              last_name = VALUES(last_name),
              balance = VALUES(balance),
              level = VALUES(level),
              last_activity = VALUES(last_activity),
              received_income_today = VALUES(received_income_today),
              free_bets_today = VALUES(free_bets_today),
              handle_preference = VALUES(handle_preference),
              welcome_sent = VALUES(welcome_sent),
              timezone = VALUES(timezone),
              location = VALUES(location)
        ";

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $user->user_id);
        $query->bindValue(':first_name', $user->first_name);
        $query->bindValue(':user_name', $user->user_name);
        $query->bindValue(':last_name', $user->last_name);
        $query->bindValue(':balance', $user->balance);
        $query->bindValue(':level', $user->level);
        $query->bindValue(':last_activity', $user->last_activity);
        $query->bindValue(':received_income_today', $user->received_income_today);
        $query->bindValue(':free_bets_today', $user->free_bets_today);
        $query->bindValue(':handle_preference', $user->handle_preference);
        $query->bindValue(':welcome_sent', $user->welcome_sent);
        $query->bindValue(':timezone', $user->timezone);
        $query->bindValue(':location', $user->location);
        return $query->execute();

        //return $this->updateObject('users', $user);
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function doesUserExistById($user_id)
    {
        return $this->doesItemExist('users', 'user_id', $user_id);
    }

    public function doesUserExistByUserName($user_name)
    {
        return $this->doesItemExist('users', 'user_name ', $user_name );
    }

    /**
     * @param $user_id
     * @return \GroupBot\Types\User
     */
    public function getUserFromId($user_id)
    {
        $sql = 'SELECT * FROM users
        WHERE user_id = :user_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $user_id);
        $query->execute();

        if ($query->rowCount()) {
            $query->setFetchMode(\PDO::FETCH_CLASS, 'GroupBot\Types\User');
            return $query->fetch();
        }
        return false;
    }

    /**
     * @param Chat $chat
     * @param $name
     * @return \GroupBot\Types\User[]|bool
     */
    public function getUsersInChatWithName(Chat $chat, $name)
    {
        $sql = 'SELECT
                  u.*
                FROM stats as s
                INNER JOIN users as u
                ON s.user_id = u.user_id
                WHERE s.chat_id = :chat_id AND s.user_in_chat = 1 AND :name IN (last_name, first_name, user_name)';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $chat->id);
        $query->bindValue(':name', $name);
        $query->execute();

        if ($query->rowCount()) {
            return $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Types\User');
        }
        return false;
    }

    /**
     * @param $name
     * @return \GroupBot\Types\User[]|bool
     */
    public function getUsersWithName($name)
    {
        $sql = 'SELECT * FROM users
        WHERE :name IN (last_name, first_name, user_name)';

        $query = $this->db->prepare($sql);
        $query->bindValue(':name', $name);
        $query->execute();

        if ($query->rowCount()) {
            return $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Types\User');
        }
        return false;
    }

    /**
     * @param $user_name
     * @return \GroupBot\Types\User
     */
    public function getUserFromUserName($user_name)
    {
        $sql = 'SELECT * FROM users
        WHERE user_name = :user_name';

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_name', $user_name);
        $query->execute();

        if ($query->rowCount()) {
            $query->setFetchMode(\PDO::FETCH_CLASS, 'GroupBot\Types\User');
            return $query->fetch();
        }
        return false;
    }

    /**
     * @return \GroupBot\Types\User[]|bool
     */
    public function getAllUsers($include_bank)
    {
        $sql = 'SELECT * FROM users';
        if (!$include_bank) $sql .= ' WHERE user_name != "'. COIN_TAXATION_BODY . '"';

        $query = $this->db->prepare($sql);
        $query->execute();

        if ($query->rowCount()) {
            return $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Types\User');
        }
        return false;
    }

    /**
     * @param \GroupBot\Types\User $user
     * @return Chat[]|bool
     */
    public function getActiveChatsByUser(\GroupBot\Types\User $user)
    {
        $sql = 'SELECT chats.* 
                FROM stats
                INNER JOIN chats
                ON chats.chat_id = stats.chat_id
                WHERE stats.user_id = :user_id AND stats.user_in_chat = TRUE AND chats.chat_id != :user_id2
                ORDER BY stats.lastpost_date DESC';

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $user->user_id);
        $query->bindValue(':user_id2', $user->user_id);
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

    /**
     * @param Chat $chat
     * @param int $weeks_since_last_post
     * @return \GroupBot\Types\User[] | bool
     */
    public function getActiveUsersInChat(Chat $chat, $weeks_since_last_post = 4)
    {
        $sql = 'SELECT
                     u.*
                FROM stats as s
                INNER JOIN users as u
                ON s.user_id = u.user_id
                WHERE s.chat_id = :chat_id AND s.user_in_chat = 1 AND s.lastpost_date > NOW() - INTERVAL :no_weeks WEEK';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $chat->id);
        $query->bindValue(':no_weeks', $weeks_since_last_post, \PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount()) {
            return $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Types\User');
        }
        return false;
    }

    /**
     * @param $chat_id
     * @return bool|\GroupBot\Types\User[]
     * @internal param Chat $chat
     */
    public function getAllUsersInChat($chat_id)
    {
        $sql = 'SELECT
                    u.*
                FROM stats as s
                INNER JOIN users as u
                ON s.user_id = u.user_id
                WHERE s.chat_id = :chat_id AND s.user_in_chat = 1';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $chat_id);
        $query->execute();

        if ($query->rowCount()) {
            return $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Types\User');
        }
        return false;
    }

    /**
     * @param bool $include_bank
     * @return int
     */
    public function getTotalNumberOfUsers($include_bank)
    {
        $sql = 'SELECT id FROM users';
        if (!$include_bank) $sql .= ' WHERE user_name != "'. COIN_TAXATION_BODY . '"';

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->rowCount();
    }

    /**
     * @param Chat $chat
     * @param \GroupBot\Types\User $user
     * @param bool $user_in_chat
     * @return bool
     */
    public function updateWhetherUserIsInChat(Chat $chat, \GroupBot\Types\User $user, $user_in_chat)
    {
        $sql = 'UPDATE stats SET user_in_chat = :user_in_chat WHERE user_id = :user_id AND chat_id = :chat_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':user_in_chat', $user_in_chat);
        $query->bindValue(':chat_id', $chat->id);
        $query->bindValue(':user_id', $user->user_id);
        return $query->execute();
    }

    /**
     * @param Message $message
     * @return bool
     */
    public function updateUserMessageStats(Message $message)
    {
        $sql = 'INSERT INTO stats (chat_id, user_id, posts, posts_today, lastpost, lastpost_date, user_in_chat)
                VALUES (:chat_id, :user_id, 1, 1, :lastpost, NOW(), 1)
                ON DUPLICATE KEY UPDATE
                posts = posts + 1, posts_today = posts_today + 1, lastpost = VALUES(lastpost), lastpost_date = VALUES(lastpost_date), user_in_chat = 1';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $message->Chat->id);
        $query->bindValue(':user_id', $message->User->user_id);
        $query->bindValue(':lastpost', isset($message->raw_text) ? $message->raw_text : '');
        return $query->execute();
    }

    /**
     * @param Message $message
     * @return bool
     */
    public function updateUserCommandStats(Message $message)
    {
        $sql = 'INSERT INTO stats_commands (command, chat_id, user_id, uses, uses_today, last_used)
                VALUES (:command, :chat_id, :user_id, 1, 1, NOW())
                ON DUPLICATE KEY UPDATE
                command = VALUES(command), uses = uses + 1, uses_today = uses_today + 1, last_used = NOW()';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $message->Chat->id);
        $query->bindValue(':user_id', $message->User->user_id);
        $query->bindValue(':command', $message->command);
        return $query->execute();
    }

    /**
     * @param $chat_id
     * @param $user_id
     * @return array|bool
     */
    public function getUserCommandStatsInChat($chat_id, $user_id)
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

    /**
     * @param Chat $chat
     * @param \GroupBot\Types\User $user
     * @return bool|UserPostStats
     */
    public function getUserPostStatsInChat(Chat $chat, \GroupBot\Types\User $user)
    {
        $sql = 'SELECT posts, posts_today, lastpost, lastpost_date
                FROM stats
                WHERE chat_id = :chat_id AND user_id = :user_id';

        $query = $this->db->prepare($sql);
        $query->bindValue(':chat_id', $chat->id);
        $query->bindValue(':user_id', $user->user_id);
        $query->execute();

        if ($query->rowCount()) {
            $query->setFetchMode(\PDO::FETCH_CLASS, 'GroupBot\Types\UserPostStats');
            /** @var UserPostStats $userPostStats */
            $userPostStats = $query->fetch();
            $userPostStats->User = $user;
            $userPostStats->Chat = $chat;
            return $userPostStats;
        }
        return false;
    }
}
