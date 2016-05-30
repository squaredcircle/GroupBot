<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:07 AM
 */
namespace GroupBot\Command\misc;


use GroupBot\Database\User;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class time extends Command
{
    private function getChatTimeZones()
    {
        $DbUser = new User($this->db);
        $users = $DbUser->getAllUsersInChat($this->Message->Chat->id);

        $timezones = [];

        foreach ($users as $user) {
            if (isset($user->timezone)) {
                $timezones[$user->timezone][] = $user->getName();
            }
        }
        $timezones['Australia/Perth'][] = 'ShitBot';

        $out = '';

        foreach ($timezones as $timezone => $people) {
            $date = new \DateTime("now", new \DateTimeZone($timezone));
            $out .= emoji(0x1F551) . " *" . $timezone . "*: `" . $date->format('g:i A') . "`";
            $out .= "\n`       `_(";
            foreach ($people as $person) {
                $out .= "$person, ";
            }
            $out = substr($out,0,-2);
            $out .= ")_\n\n";
        }

        return $out;
    }

    public function main()
    {
        if ($this->isParam()) {
            if (in_array($this->getParam(), timezone_identifiers_list())) {
                $this->Message->User->timezone = $this->getParam();
                $this->Message->User->save($this->db);
                $out = emoji(0x1F44D) . " Your timezone has been updated!\n\n";
                $out .= $this->getChatTimeZones();
            } else {
                $out = emoji(0x274C) . " Can't find that timezone. Take a look here: http://php.net/manual/en/timezones.php \n\n";
            }
        } else {
            $out = $this->getChatTimeZones();
        }

        Telegram::talk($this->Message->Chat->id, $out);
    }
}