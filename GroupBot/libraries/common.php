<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 6/11/2015
 * Time: 2:18 PM
 */

function italic($text)
{
    return '_' . $text . '_';
}

function bold($text)
{
    return '*' . $text . '*';
}

function mono($text)
{
    return '`' . $text . '`';
}

function hyperlink($title, $url)
{
    return '[' . $title . '](' . $url . ')';
}

if (!function_exists('emoji')) {
    function emoji($string)
    {
        return iconv('UCS-4LE', 'UTF-8', pack('V', intval($string, 0)));
    }
}

function randomEmoji()
{
    return emoji(mt_rand(0x1F300, 0x1F5FF));
}

function randomEmojiList($list)
{
    return emoji($list[mt_rand(0,count($list)-1)]);
}

function random_pic($dir)
{
    $files = glob($dir . '/*.*');
    $file = array_rand($files);
    return $files[$file];
}

function person($text, $clip)
{
    $say = $clip ? substr($text, 0, 25) : $text;

    $out = "";

    $out .= "`.  ` ";
    $out .= randomEmojiList(array(0x1F3A9, 0x1F451, 0x1F393)); // hat, crown, graduation cap,

    $out .= "\n`.`";
    $out .= emoji(0x1F442); // ear
    $out .= randomEmojiList(array(0x1F440, 0x1F453)); // eyes, glasses
    $out .= emoji(0x1F442); // ear

    $out .= "\n`.`";
    //$out .= $this->emoji(0x1F538); // space
    $out .= "`  ` ";
    $out .= emoji(0x1F443); // nose, pignose,

    $out .= "\n`.`";
    $out .= "`  ` ";
    $out .= randomEmojiList(array(0x1F444, 0x1F445)); // mouth, tongue,

    $out .= "---->| " . $say . " |"; // max 28

    $out .= "\n`.`"; ; // left, up, down, fist, wave, ok, thumbsup, down
    $out .= randomEmojiList(array(0x1F448, 0x1F446, 0x1F447, 0x1F44A, 0x1F44B, 0x1F44D,
        0x1F44E, 0x1F52B, 0x1F50D, 0x1F4E2, 0x1F4AA)); //pistol, mag glass, megaphone, biceps
    $out .= randomEmojiList(array(0x1F455, 0x1F454, 0x1F458, 0x1F45A)); // shirt, necktie, kimono, womans shirt
    $out .= randomEmojiList(array(0x1F446, 0x1F449, 0x1F447, 0x1F44A, 0x1F44D, 0x1F44E, 0x1F52A, 0x1F50E)); //knife, mag glass

    $out .= "\n`.`";
    $out .= "`  ` ";
    $out .= emoji(0x1F456); // pants

    $out .= "\n`.`";

    $shoes = randomEmojiList(array(0x1F45E, 0x1F45F, 0x1F460, 0x1F460)); // shoes, sneakers, high heels, boots;
    $out .= "` `";
    $out .= $shoes;
    $out .= "` `";
    $out .= $shoes;

    return $out;
}