<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:07 AM
 */
namespace GroupBot\Command;

use GroupBot\Telegram;
use GroupBot\Types\Command;

class roll extends Command
{
    public function main()
    {
        if ($this->Message->text == 'dubs')
        {

            $no = mt_rand(0,9);

            Telegram::talk($this->Message->Chat->id, "`" . str_repeat($no, 9) . "`");

            return true;
        }

        $q = explode("d", trim($this->Message->text));

        if (count($q) == 2 && is_numeric($q[0]) && is_numeric($q[1]))
        {
            $no = intval($q[0]);
            $sides = intval($q[1]);

            if ($no <0 || $sides < 0 || $no > 50 || $sides > 999999999)
            {
                Telegram::talk($this->Message->Chat->id, "naw brah");
                return false;
            }

            $out = array_map("mt_rand", array_fill(0, $no, 1), array_fill(0, $no, $sides));

            $out = implode(", ", $out);

            Telegram::talk($this->Message->Chat->id, "`" . $out . "`");
        } else {
            $out = mt_rand(0, 999999999);

            $dubs = array(9 => "nines", 8 => "eights", 7 => "sevens", 6 => "sixes", 5 => "quints", 4 => "quads", 3 => "trips", 2 => "dubs");

            foreach ($dubs as $key => $value)
            {
                $test = substr(strval($out), 0 - $key);
                if (preg_match('/^(.)\1*$/', $test))
                {
                    $text = str_repeat(emoji(0x1F389), $key-1) . "`" . sprintf('%09d', $out) . "`" . "\nnice " . $value . " brah" .  str_repeat("!", $key-1);
                    Telegram::talk($this->Message->Chat->id, $text);
                    return true;
                }
            }

            Telegram::talk($this->Message->Chat->id, "`" . sprintf('%09d', $out) . "`");
        }
        return true;
    }
}