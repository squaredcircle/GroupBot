<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 19/11/2015
 * Time: 11:01 AM
 */
namespace GroupBot\Command;

use GroupBot\Base\Logging;
use GroupBot\Types\Command;

class t_stats extends Command
{
    private function plural_grammar($no)
    {
        if ($no == 1)
            return '';
        else
            return 's';
    }

    public function t_stats()
    {
        $log = new Logging($this->Message);

        if ($this->isParam()) {
            $user_id = $log->checkIfUserIsLogged($this->getParam());
            if (!$user_id) {
                $this->Telegram->talk($this->Message->Chat->id, "can't find that user, brah");
                return false;
            }
        } else {
            $user_id = $this->Message->User->id;
        }

        $log = $log->getAllUserLogsForChat($user_id);

        $date = 0;
        foreach ($log->LogsCommand as $cmd) {
            if (strtotime($cmd->last_used) > $date) {
                $last_cmd = $cmd;
                $date = strtotime($cmd->last_used);
            }
        }
        if (!isset($last_cmd)) return false;

        $out = "*" . $this->Message->Chat->title . "* stats for *" .$log->User->first_name . " " . $log->User->last_name . "*."
            . "\n`   `•` " . $log->posts_today . "` message" . $this->plural_grammar($log->posts_today) . " sent today"
            . "\n`   `•` " . $log->posts       . "` message" . $this->plural_grammar($log->posts)       . " sent ever"
            . "\n`   `•` " . round(86400 * $log->posts / (strtotime("now") - strtotime("2015-11-19 11:00:00")), 0) . "` messages sent per day, on average"
            . "\nLast message (`" . date('D jS g:iA', strtotime($log->lastpost_date)) . "`):"
            . "\n`   `_" . $log->lastpost . "_"
            . "\nLast command: `" . $last_cmd->command . "`"
            . "\n`   `•` " . $last_cmd->uses_today . "` use" . $this->plural_grammar($last_cmd->uses_today) . " today"
            . "\n`   `•` " . $last_cmd->uses       . "` use" . $this->plural_grammar($last_cmd->uses_today) . " ever";

        $this->Telegram->talk($this->Message->Chat->id, $out);
        return true;
    }
}