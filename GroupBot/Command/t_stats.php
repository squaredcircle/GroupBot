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
        $log = $log->getAllUserLogsForChat();

        $date = 0;
        $last_cmd = NULL;
        foreach ($log->LogsCommand as $cmd) {
            if ($cmd->last_used > $date) $last_cmd = $cmd;
        }

        $out = "In this chat, _" . $this->Message->Chat->title . "_, *" .$log->User->first_name . " " . $log->User->last_name
            . "* has sent *" . $log->posts_today . "* message" . $this->plural_grammar($log->posts_today) . " today, and *" . $log->posts
            . "* message" . $this->plural_grammar($log->posts) . " ever.\nThe last message was sent at *"
            . date('D jS g:iA', strtotime($log->lastpost_date)) . "*, and read:\n_" . $log->lastpost . "_\nThe last command used was */"
            . $last_cmd->command . "*, which they used *" . $last_cmd->uses_today . "* time" . $this->plural_grammar($last_cmd->uses_today)
            . " today and *" . $last_cmd->uses . "* time" . $this->plural_grammar($last_cmd->uses) . " ever.";

        $this->Telegram->talk($this->Message->Chat->id, $out);
    }
}