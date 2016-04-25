<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:07 AM
 */
namespace GroupBot\Command\reminder;

use GroupBot\Brains\Reminder\Control;
use GroupBot\Brains\Reminder\Types\Reminder;
use Carbon\Carbon;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class remind extends Command
{
    public function main()
    {
        if ($this->isParam() && $this->noParams() > 2) {
            $params = explode(' to ', $this->getAllParams(), 2);
            if (count($params) == 2) {
                try {
                    $date_due = Carbon::parse($params[0]);
                    $reminder = new Reminder();
                    $reminder->construct($this->Message->User->user_id,
                        $this->Message->Chat->id,
                        Carbon::now()->toDateTimeString(),
                        $date_due->toDateTimeString(),
                        $params[1]
                    );
                    $ReminderControl = new Control($this->db);
                    $ReminderControl->addReminder($reminder);

                    $diff = $date_due->diffForHumans(Carbon::now(), true);
                    $out = emoji(0x23F2) . " Okay, I've scheduled a reminder in *$diff*.";
                    if ($date_due->diffInMinutes(Carbon::now()) < 60) {
                        $out .= "\n\nBy the way, I only send out notifications once a minute.";
                    }

                    Telegram::talk($this->Message->Chat->id, $out);
                    return true;
                } catch (\Exception $e) {
                    // nada
                }
            }
        }

        $out = emoji(0x270D) . " Like this fam: "
            . "\n"
            . "\n`   `• `/remind` *+2 hours* `to` *pick up the milk*"
            . "\n`   `• `/remind` *2:25PM 26th April* `to` *look out the window*"
            . "\n`   `• `/remind` *tomorrow 5PM* `to` *go outside*"
            . "\n`   `• `/remind` *monday 2AM* `to` *go to sleep*"
            . "\n"
            . "\nMy timezone is *GMT+8*, and it's now *" . date('g:i A') . "* on the *" . date('jS') . "*.";
        Telegram::talk($this->Message->Chat->id, $out);
        return true;
    }
}