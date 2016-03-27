<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 23/02/2016
 * Time: 11:29 AM
 */

namespace GroupBot\Database;


class Cron extends DbConnection
{
    public function resetChatMessagesThisMinute()
    {
        $sql = 'UPDATE chats SET messages_sent_last_min = 0';
        $query = $this->db->prepare($sql);
        return $query->execute();
    }

    public function resetDailyCounters()
    {
        $sql1 = 'UPDATE stats SET posts_today = 0';
        $sql2 = 'UPDATE stats_commands SET uses_today = 0';
        $sql3 = 'UPDATE users SET free_bets_today = 0, received_income_today = 0';
        $query1 = $this->db->prepare($sql1);
        $query2 = $this->db->prepare($sql2);
        $query3 = $this->db->prepare($sql3);
        return $query1->execute() && $query2->execute() && $query3->execute();
    }
}