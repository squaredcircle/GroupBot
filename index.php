<?php

require(__DIR__ . '/GroupBot/Settings.php');

function apiRequestWebhook($method, $parameters)
{
  if (!is_string($method))
  {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters)
  {
    $parameters = array();
  }
  else if (!is_array($parameters))
  {
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
	$text = $message['text'];

	if (isset($text) && (stripos($text, 'shitbot') !== false))
    {
      talk($chat_id, emoji(0x1F527) . " Upgrading fam, go away\nTry talking to Blimpbot instead");
    } elseif ($text[0] == '/') {
      if (mt_rand(0,9) > 7) talk($chat_id, emoji(0x1F634) . " m8 stop bugging me I'm sleeping");
    }
}

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) exit;

if (isset($update["message"]))
  processMessage($update["message"]);
