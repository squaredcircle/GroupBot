<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:07 AM
 */
namespace GroupBot\Command\misc;


use Carbon\Carbon;
use GroupBot\Database\User;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class TimeData
{
    /** @var  string[] */
    public $users;

    /** @var  Carbon */
    public $date;

    public function __construct(array $users, Carbon $date)
    {
        $this->users = $users;
        $this->date = $date;
    }
}

class time extends Command
{
    private function getUserTimeZones()
    {
        $DbUser = new User($this->db);

        if ($this->Message->Chat->id == '56390227') {
            $users = $DbUser->getAllUsersInChat('-1001033369096');
        } else {
            $users = $DbUser->getAllUsersInChat($this->Message->Chat->id);
        }
        $timezones = [];
        foreach ($users as $user) {
            if (isset($user->timezone)) {
                $timezones[$user->timezone][] = $user->getName();
            }
        }
        $timezones['Australia/Sydney'][] = 'ShitBot';
        return $timezones;
    }

    /**
     * @param $timezones
     * @return TimeData[]
     */
    private function sortTimeZones($timezones)
    {
        $tzs = [];
        foreach ($timezones as $timezone => $users) {
            $tzs[] = new TimeData($users, Carbon::now($timezone));
        }
        usort($tzs, function ($a, $b) {
            if ($a->date->offset == $b->date->offset) return 0;
            if ($a->date->offset < $b->date->offset) return 1;
            return -1;
        });
        return $tzs;
    }

    private function getClockEmoji(Carbon $time)
    {
        if ($time->format('i') >= 45) {
            $hour = $time->addHour()->format('g');
            $minute = '00';
            $time->subHour(1);
        } elseif ($time->format('i') >= 15) {
            $hour = $time->format('g');
            $minute = '30';
        } else {
            $hour = $time->format('g');
            $minute = '00';
        }

        //$hour = $time->format('g');
        //$minute = ($time->format('i') > 30) ? '30' : '00';

        if ($minute == '30') {
            return emoji(0x1F55B + (int)$hour);
        } else {
            return emoji(0x1F550 + (int)$hour - 1);
        }
    }

    private function printTimeZones()
    {
        $timezones = $this->getUserTimeZones();
        $tzs = $this->sortTimeZones($timezones);

        $out = '';

        foreach ($tzs as $tz) {
            $out .= $this->getClockEmoji($tz->date) . " <code>" . $tz->date->format('D h:iA') . " </code><b> " . $tz->date->tzName . "</b> <i>(";
            foreach ($tz->users as $user) {
                $out .= $user . ", ";
            }
            $out = substr($out,0,-2);
            $out .= ")</i>\n";
        }

        return $out;
    }

    private function updateTimeZone(\GroupBot\Types\User $user, $timezone)
    {
        if (in_array($timezone, timezone_identifiers_list())) {
            $user->timezone = $this->getParam();
            return $user->save($this->db);
        }
        return false;
    }

    public function main()
    {
        if ($this->isParam()) {
            if ($this->updateTimeZone($this->Message->User, $this->getParam())) {
                $out = emoji(0x1F44D) . " Your timezone has been updated!\n\n";
                $out .= $this->printTimeZones();
            } else {
                $out = emoji(0x274C) . " Can't find that timezone. Try something like: \n<code>   /time {Continent}/{City}</code>.\n\nTake a look here for all the options available:\n http://php.net/manual/en/timezones.php";
            }
        } else {
            $out = $this->printTimeZones();
        }

        Telegram::talk_html($this->Message->Chat->id, $out);
    }
}
