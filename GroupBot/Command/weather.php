<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:07 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Weather\Realtime;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class weather extends Command
{
    public function main()
    {
        $today = $this->today();
        $index = $this->uv_index();
        $sun = $this->sun();

        $out = emoji(0x26C5) . " Weather for *" . $header->name . "*, *" . $header->state . "*."
            . "\n"
            . "\n`   `• Currently *" . $now->air_temp . "°C*"
            . "\n`   `• " . $today[1] . " Max *" . $today[0] . "°C* today"
            . "\n`   `• The UV index is at *" . $index . "* ([" . $this->uv_code($index)[1] . "](http://www.arpansa.gov.au/uvindex/realtime/images//per_rt.gif))"
            . "\n`   `• Sunrise is at *$sun[0]* today, and sunset at *$sun[1]*";


        Telegram::talk($this->Message->Chat->id, $out, true);
    }
}