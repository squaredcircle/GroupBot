<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 2/03/2016
 * Time: 11:58 PM
 */

namespace GroupBot\Brains;


use Carbon\Carbon;
use GroupBot\Database\User;
use GroupBot\Types\Chat;

class Query
{
    /**
     * @param \PDO $db
     * @param Chat $chat
     * @param $name
     * @return \GroupBot\Types\User|string
     */
    public static function getUserMatchingStringOrErrorMessage(\PDO $db, Chat $chat, $name)
    {
        $userSQL = new User($db);
        $userInChat = true;

        if (isset($chat)) {
            if (!$user_receiving = $userSQL->getUsersInChatWithName($chat, $name)) {
                if (!$user_receiving = $userSQL->getUsersWithName($name)) {
                    return emoji("0x1F44E") . " Can't find any users matching `" . $name . "`, brah";
                }
                $userInChat = false;
            }
        } else {
            if (!$user_receiving = $userSQL->getUsersWithName($name)) {
                return emoji("0x1F44E") . " Can't find any users matching `" . $name . "`, brah";
            }
            $userInChat = false;
        }

        if (count($user_receiving) > 1) {
            $out = "There are " . count($user_receiving) . " users matching that name" . ($userInChat ? " in this chat:" : ":");
            foreach ($user_receiving as $user) {
                $out .= "\n`   `•` " .$user->getNameLevelAndTitle();
            }
            $out .= "Please redefine your query";
            return $out;
        }
        return $user_receiving[0];
    }

    public static function getChatsByScore(\PDO $db)
    {
        $sql = 'SELECT *
                FROM chats
                WHERE type = 2 OR type = 3         
        ';

        $query = $db->prepare($sql);
        $query->execute();

        if ($query->rowCount()) {
            /** @var Chat[] $chats */
            $chats = $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Types\Chat');
            foreach ($chats as $chat) {
                $chat->id = $chat->chat_id;
            }
        } else {
            return false;
        }

        $DbUser = new User($db);
        $DbChat = new \GroupBot\Database\Chat($db);
        $out = array();

        foreach ($chats as $chat)
        {
            $users = $DbUser->getAllUsersInChat($chat->id);
            $no_users = count($users);
            $lastpost = $DbChat->getChatLastPostDate($chat->id);

            $date = Carbon::parse($lastpost);

            if ($date->lt(Carbon::now()->subWeek())) continue;
            if ($no_users < 5) continue;

            $avg_level = array_reduce($users, function($i, $user)
            {
                return $i += $user->level;
            });

            $avg_level = $avg_level != null ? round($avg_level / count($users),0) : '0';

            $out[] = [$chat, $avg_level];
        }

        usort($out, function($a, $b)
        {
            if ($a[1] == $b[1]) return 0;
            if ($a[1] < $b[1]) return 1;
            return -1;
        });

        return $out;
    }

    public static function getUsersByLevel(\PDO $db, Chat $chat = NULL, $include_bank = true, $ascending = true, $no_users = NULL)
    {
        if (isset($chat)) {
            $sql = 'SELECT
                     s.user_id
                    ,u.user_name
                    ,u.first_name
                    ,u.last_name
                    ,u.balance
                    ,u.level
                    ,u.last_activity
                    ,u.received_income_today
                    ,u.free_bets_today
                    ,u.handle_preference
                FROM stats AS s
                INNER JOIN users AS u
                ON s.user_id = u.user_id
                WHERE s.chat_id = :chat_id AND s.user_in_chat = 1';
            if (!$include_bank)
                $sql .= ' AND user_id != ' . COIN_BANK_ID;
        } else {
            $sql = 'SELECT user_id, user_name, first_name, last_name, balance, level, last_activity, received_income_today, free_bets_today, handle_preference FROM users';
            if (!$include_bank)
                $sql .= ' WHERE user_id != ' . COIN_BANK_ID;
        }
        $sql .= ' ORDER BY level ' . ($ascending ? 'ASC ' : 'DESC ') . ', balance '. ($ascending ? 'ASC ' : 'DESC ');
        if (isset($no_users))
            $sql .= 'LIMIT :no_users';

        $query = $db->prepare($sql);
        if (isset($chat))
            $query->bindValue(':chat_id', $chat->id);
        if (isset($no_users))
            $query->bindValue(':no_users', $no_users, \PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount()) {
            return $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Types\User');
        }
        return false;
    }

    public static function getUsersByMoneyAndLevel(\PDO $db, Chat $chat = NULL, $include_bank = true, $ascending = true, $no_users = NULL)
    {
        if (isset($chat)) {
            $sql = 'SELECT
                     s.user_id
                    ,u.user_name
                    ,u.first_name
                    ,u.last_name
                    ,u.balance
                    ,u.level
                    ,u.last_activity
                    ,u.received_income_today
                    ,u.free_bets_today
                    ,u.handle_preference
                FROM stats as s
                INNER JOIN users as u
                ON s.user_id = u.user_id
                WHERE s.chat_id = :chat_id AND s.user_in_chat = 1';
            if (!$include_bank) $sql .= ' AND user_id != ' . COIN_BANK_ID;
        } else {
            $sql = 'SELECT user_id, user_name, first_name, last_name, balance, level, last_activity, received_income_today, free_bets_today, handle_preference FROM users';
            if (!$include_bank) $sql .= ' WHERE user_id != ' . COIN_BANK_ID;
        }
        $sql .= ' ORDER BY balance + 5 * level * (level + 1) - 10 ' . ($ascending ? 'ASC ' : 'DESC ');
        if (isset($no_users)) $sql .= 'LIMIT :no_users';

        $query = $db->prepare($sql);
        if (isset($chat)) $query->bindValue(':chat_id', $chat->id);
        if (isset($no_users)) $query->bindValue(':no_users', $no_users, \PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount()) {
            return $query->fetchAll(\PDO::FETCH_CLASS, 'GroupBot\Types\User');
        }
        return false;
    }

    public static function getGlobalRanking(\PDO $db, \GroupBot\Types\User $user)
    {
        $leaderboard = Query::getUsersByMoneyAndLevel($db, NULL, true, false);

        foreach ($leaderboard as $key => $usr) {
            if ($usr->user_id == $user->user_id) return $key + 1;
        }

        return false;
    }
}
