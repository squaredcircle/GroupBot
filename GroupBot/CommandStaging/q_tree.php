<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:21 AM
 */
namespace GroupBot\Command;

use GroupBot\Telegram;
use GroupBot\Types\Command;

class q_tree extends Command
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

    private function present($repeat)
    {
        $present = emoji(0x1F381);
        return $this->repeat($present, $repeat);
    }

    public function q_tree()
    {
        $ribbon = emoji(0x1F380);
        $santa = emoji(0x1F385);
        $baub_yellow = emoji(0x1F315);
        $baub_black = emoji(0x1F311);
        $baub_red = emoji(0x1F534);
        $baub_blue = emoji(0x1F535);

        $out = '';

        $out .= $santa . $santa . ".`         `" .  emoji(0x2B50) . "\n";
        $out .= $santa . $santa . ".`      `" .  $this->tree(2) . "\n";
        $out .= ".`               `" .  $this->tree(2). $baub_blue . "\n";
        $out .= ".`            `" .  $this->tree(1) . $baub_red . $this->tree(2) . "\n";
        $out .= ".`         `" .  $this->tree(1) . $baub_yellow . $this->tree(1) . $baub_yellow . $this->tree(1) . "\n";
        $out .= ".`      `" .  $this->tree(1) . $ribbon . $this->tree(2) . $ribbon . $this->tree(1) . "\n";
        $out .= ".`   `" .  $this->tree(3). $baub_red . $this->tree(1). $baub_blue . $this->tree(1) . "\n";
        $out .= ".``" .  $this->tree(8) .  "\n";
        $out .= ".``" .  $this->present(8) . "\n";
        $out .= "*M E R R Y  C H R I S T M A S*";

        Telegram::talk($this->Message->Chat->id, $out);
    }
}