<?php

spl_autoload_register( function ($class) {
	$include_path = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
	if (file_exists(__DIR__ . '/' . $include_path))
		return include(__DIR__ . '/' . $include_path);
	else
		return false;
});

require 'vendor/autoload.php';

$ShitBot = new GroupBot\GroupBot();
/**
function talk($chat_id, $text)
{
    apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "text" => $text));
}

try
{

}
catch
{
    $content = file_get_contents("php://input");
    $update = json_decode($content, true);

    if (isset($update['message']))
    {
        $chat_id = $update['message']['chat']['id'];
        $message_id = $update['message']['chat']['message_id'];

        $parameters = array('method' => 'sendMessage',
            'chat_id' => $chat_id,
            'text' => '🚨 @richardstallman',
            'reply_to_message_id' => $message_id
        );

        $parameters["method"] = $method;
        header("Content-Type: application/json");
        echo json_encode($parameters);
    }
}**/