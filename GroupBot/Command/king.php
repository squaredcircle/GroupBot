<?php
namespace GroupBot\Command;

use GroupBot\Telegram;
use GroupBot\Types\Command;

class king extends Command
{

    private function repeat($emoji, $amount)
    {
        $out = '';
        for ($i = 0; $i < $amount; $i++) {
            $out .= $emoji;
        }
        return $out;
    }

    private function queen($repeat)
    {
        $tree = '🤴👑';
        return $this->repeat($tree, $repeat);
    }

    private function king($repeat)
    {
        $santa = '👑';
        return $this->repeat($santa, $repeat);
    }

    public function main()
    {
        $jokes_file = file(__DIR__ . "/../Libraries/jokes_king");
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

        $out = $this->queen(5) . "\n";
        $out .= $jokes[mt_rand(0, count($jokes) - 1)];
        //$out .= $this->king(10);

        Telegram::talk($this->Message->Chat->id, $out);
    }
}