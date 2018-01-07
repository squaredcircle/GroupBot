<?php

function apiRequestWebhook($method, $parameters)
{
    if (!is_string($method)) {
        error_log("Method name must be a string\n");
        return false;
    }

    if (!$parameters) {
        $parameters = array();
    } else if (!is_array($parameters)) {
        error_log("Parameters must be an array\n");
        return false;
    }

    $parameters["method"] = $method;

    header("Content-Type: application/json");
    echo json_encode($parameters);
    return true;
}

function talk($chat_id, $text)
{
    apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "text" => $text));
}

function emoji($string)
{
    return iconv('UCS-4LE', 'UTF-8', pack('V', intval($string, 0)));
}

function processMessage($message)
{
    $chat_id = $message['chat']['id'];
    if (isset($message['text'])) $text = $message['text'];
    else return;

    if (isset($text) && (stripos($text, 'shitbot') !== false)) {
        talk($chat_id, emoji(0x1F527) . " My head hurts.");
    } elseif ($text[0] == '/') {
        if ($text == '/roll') {
            t_roll($text, $chat_id);
        } elseif (mt_rand(0, 9) > 7) {
            talk($chat_id, emoji(0x1F634) . " m8 stop bugging me I'm sleeping");
        }
    }
}

function t_roll($text, $chat_id)
{
    $out = mt_rand(0, 999999999);

    $dubs = array(9 => "nines", 8 => "eights", 7 => "sevens", 6 => "sixes", 5 => "quints", 4 => "quads", 3 => "trips", 2 => "dubs");

    foreach ($dubs as $key => $value) {
        $test = substr(strval($out), 0 - $key);
        if (preg_match('/^(.)\1*$/', $test)) {
            $text = str_repeat(emoji(0x1F389), $key - 1) . sprintf('%09d', $out) . "\nnice " . $value . " brah" . str_repeat("!", $key - 1);
            talk($chat_id, $text);
            return true;
        }
    }

    talk($chat_id, sprintf('%09d', $out));
    return true;
}

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update)
    exit;

if (isset($update["message"]))
    processMessage($update["message"]);
