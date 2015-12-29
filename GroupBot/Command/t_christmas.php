<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:12 AM
 */
namespace GroupBot\Command;

use GroupBot\Types\Command;

class t_christmas extends Command
{
    private function repeat($emoji, $amount)
    {
        $out = '';
        for ($i = 0; $i < $amount; $i++) {
            $out .= $emoji;
        }
        return $out;
    }

    private function tree($repeat)
    {
        $tree = emoji(0x1F384);
        return $this->repeat($tree, $repeat);
    }

    private function santa($repeat)
    {
        $santa = emoji(0x1F385);
        return $this->repeat($santa, $repeat);
    }
    public function t_christmas()
    {
        $jokes_file = file(__DIR__ . "/../libraries/jokes_christmas");
        $jokes = array();

        $joke = "";

        foreach ($jokes_file as $line) {
            if ($line == "\r\n") {
                $jokes[] = $joke;
                $joke = "";
                continue;
            }
            $joke .= $line;
        }

        $out = $this->tree(15) . "\n";
        $out .= $jokes[mt_rand(0, count($jokes) - 1)];
        $out .= $this->santa(15);

        $this->Telegram->talk($this->Message->Chat->id, $out);
    }
}