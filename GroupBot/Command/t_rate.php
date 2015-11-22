<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 11:17 PM
 */
namespace GroupBot\Command;

use GroupBot\Types\Command;

class t_rate extends Command
{
    public function t_rate()
    {
        $ratings = array(
            0 =>  "*0/10* see me after class",
            1 =>  "*1/10* see me after class",
            2 =>  "*2/10* u srs?",
            3 =>  "*3/10* brah...",
            4 =>  "*4/10* try harder next time",
            5 =>  "*5/10* eh",
            6 =>  "*6/10* k",
            7 =>  "*7/10* good fam",
            8 =>  "*8/10* gr8",
            9 =>  "*9/10* bretty gr8 m8",
            10 => "*10/10* have a sticker " . emoji(mt_rand(0x1F40C, 0x1F43C))
        );
        $this->Telegram->talk($this->Message->Chat->id, $ratings[mt_rand(0,10)]);
    }
}