<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 24/04/2016
 * Time: 8:57 PM
 */

namespace GroupBot\Brains\Reminder;


use Carbon\Carbon;
use GroupBot\Brains\Reminder\Types\Reminder;
use GroupBot\Database\User;
use GroupBot\Telegram;

class Control
{
    /** @var SQL  */
    private $SQL;

    /** @var \PDO  */
    private $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
        $this->SQL = new SQL($db);
    }

    public function sendReminders()
    {
        $reminders = $this->SQL->select_reminders();
        $UserDb = new User($this->db);

        /** @var Reminder $reminder */
        foreach ($reminders as $reminder) {
            $date_due = Carbon::parse($reminder->date_due);
            $date_created = Carbon::parse($reminder->date_created);
            if ($date_due->lte(Carbon::now()))
            {
                $user = $UserDb->getUserFromId($reminder->user_id);
                $out = emoji(0x23F0) . " *" . $user->getName() . "*, your reminder from *" . $date_created->diffForHumans(Carbon::now(), true) . "* ago:"
                    . "\n"
                    . "\n`" . $reminder->content . "`";
                Telegram::talkForced($reminder->chat_id, $out);
                $this->SQL->delete_reminder($reminder);
            }
        }
    }

    public function addReminder(Reminder $reminder)
    {
        if (Carbon::parse($reminder->date_due)->gt(Carbon::parse($reminder->date_created))) {
            return $this->SQL->insert_reminder($reminder);
        }
        return false;
    }
}