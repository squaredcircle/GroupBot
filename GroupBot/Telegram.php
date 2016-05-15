<?php

namespace GroupBot;

require_once(__DIR__ . '/Settings.php');

class Telegram
{
	public function __construct() {}

	public static function customShitpostingMessage($text)
	{
		self::talkForced(SHITPOSTING_ID, $text);
	}

	public static function broadcast($text, $channel)
	{
		self::apiRequest("sendMessage", array('chat_id' => '@' . $channel, "text" => $text, "parse_mode" => "Markdown"));
	}

	public static function kick($chat_id, $user_id)
	{
		self::apiRequest("kickChatMember", array(
			'chat_id' => $chat_id,
			"user_id" => $user_id
		));
	}

	public static function kick2($chat_id, $user_id)
	{
		return self::apiRequest("kickChatMember", array(
			'chat_id' => $chat_id,
			"user_id" => $user_id
		));
	}

	public static function talk($chat_id, $text, $disable_web_page_preview = false)
	{
		self::apiRequestWebhook("sendMessage", array(
			'chat_id' => $chat_id,
			"text" => $text,
			"parse_mode" => "Markdown",
			"disable_web_page_preview" => $disable_web_page_preview
		));
	}

	public static function talkForced($chat_id, $text)
	{
		self::apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $text, "parse_mode" => "Markdown"));
	}

	public static function talk_suppress($chat_id, $text)
	{
		self::apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "text" => $text, "parse_mode" => "Markdown", "disable_web_page_preview" => "true"));
	}

	public static function talk_inline_keyboard($chat_id, $text, $keyboard)
	{
		self::apiRequestWebhook("sendMessage",
			array(
				'chat_id' => $chat_id,
				"text" => $text,
				"parse_mode" => "Markdown",
				"reply_markup" => array(
					"inline_keyboard" => $keyboard
				)
			)
		);
	}

	public static function edit_inline_message($chat_id, $message_id, $text, $keyboard = NULL)
	{
		if (isset($keyboard)) {
			self::apiRequestWebhook("editMessageText",
				array(
					'chat_id' => $chat_id,
					'message_id' => $message_id,
					"text" => $text,
					"parse_mode" => "Markdown",
					"reply_markup" => array(
						"inline_keyboard" => $keyboard
					)
				)
			);
		} else {
			self::apiRequestWebhook("editMessageText",
				array(
					'chat_id' => $chat_id,
					'message_id' => $message_id,
					"text" => $text,
					"parse_mode" => "Markdown"
				)
			);
		}
	}

	public static function reply_keyboard($chat_id, $text, $message_id, $keyboard)
	{
		self::apiRequestWebhook("sendMessage",
			array(
				'chat_id' => $chat_id,
				"text" => $text,
				"reply_to_message_id" => $message_id,
				"parse_mode" => "Markdown",
				"reply_markup" => array(
					"keyboard" => $keyboard,
					"resize_keyboard" => true,
					"one_time_keyboard" => true,
					"selective" => true
				)
			)
		);
	}

	public static function talk_hide_keyboard($chat_id, $text)
	{
		self::apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "text" => $text, "parse_mode" => "Markdown",
			"reply_markup" => array("hide_keyboard" => true)));
	}

	public static function reply($chat_id, $message_id, $text)
	{
		self::apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "reply_to_message_id" => $message_id, "text" => $text, "parse_mode" => "Markdown"));
	}

	public static function answerInlineQuery($inline_query_id, $results)
	{
		self::apiRequestWebhook("answerInlineQuery", array('inline_query_id' => $inline_query_id, 'cache_time' => 0, 'results' => $results));
	}

	public static function sendChatTypingStatus($chat_id)
	{
		self::apiRequest("sendChatAction", array('chat_id' => $chat_id, 'action' => 'typing'));
	}

	public static function sendChatSendingPhotoStatus($chat_id)
	{
		self::apiRequest("sendChatAction", array('chat_id' => $chat_id, 'action' => 'upload_photo'));
	}

    public static function sendVoice($chat_id, $file_path)
    {
        $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendVoice?chat_id=" . $chat_id ;

        $post_fields = array(
            'chat_id'   => $chat_id,
            'voice'     => new \CURLFile(realpath($file_path))
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        return curl_exec($ch);
    }
	public static function sendAudio($chat_id, $file_path)
	{
		$url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendAudio?chat_id=" . $chat_id ;

		$post_fields = array(
			'chat_id'   => $chat_id,
			'audio'     => new \CURLFile(realpath($file_path))
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		return curl_exec($ch);
	}

	public static function sendDocument($chat_id, $file_path)
	{
		$url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendDocument?chat_id=" . $chat_id ;

		$post_fields = array(
			'chat_id'   => $chat_id,
			'document'     => new \CURLFile(realpath($file_path))
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		//curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file_path));
		return curl_exec($ch);
	}

	public static function customPhotoSender($chat_id, $file_path)
	{
		$url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendPhoto?chat_id=" . $chat_id ;

		$post_fields = array(
			'chat_id'   => $chat_id,
			'photo'     => new \CURLFile(realpath($file_path))
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		//curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file_path));
		return curl_exec($ch);
	}

	public static function fileIdDocumentSender($chat_id, $file_id)
	{
		self::apiRequestWebhook("sendDocument", array('chat_id' => $chat_id, 'document' => $file_id));
	}

	public static function fileIdPhotoSender($chat_id, $file_id)
	{
		self::apiRequestWebhook("sendPhoto", array('chat_id' => $chat_id, 'photo' => $file_id));
	}

	private static function apiRequestWebhook($method, $parameters)
	{
		if (!is_string($method)) {
			error_log("Method name must be a string\n");
			return false;
		}

		if (!$parameters) $parameters = array();
		else if (!is_array($parameters)) {
			error_log("Parameters must be an array\n");
			return false;
		}

		$parameters["method"] = $method;

		header("Content-Type: application/json");
		echo json_encode($parameters);
		return true;
	}

	static function apiRequest($method, $parameters)
	{
		if (!is_string($method)) {
			error_log("Method name must be a string\n");
			return false;
		}

		if (!$parameters) $parameters = array();
		else if (!is_array($parameters)) {
			error_log("Parameters must be an array\n");
			return false;
		}

		foreach ($parameters as $key => &$val) {
			// encoding to JSON array parameters, for example reply_markup
			if (!is_numeric($val) && !is_string($val)) $val = json_encode($val);
		}
		$url = API_URL.$method.'?'.http_build_query($parameters);

		$handle = curl_init($url);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($handle, CURLOPT_TIMEOUT, 60);

		return self::exec_curl_request($handle);
	}

	private static function exec_curl_request($handle)
	{
		$response = curl_exec($handle);

		if ($response === false) {
			$errno = curl_errno($handle);
			$error = curl_error($handle);
			error_log("Curl returned error $errno: $error\n");
			curl_close($handle);
			return false;
		}

		$http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
		curl_close($handle);

		if ($http_code >= 500) {
			// do not want to DDOS the server if something goes wrong
			sleep(10);
			return false;
		} 
		else if ($http_code != 200) {
			$response = json_decode($response, true);
			error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
			if ($http_code == 401) throw new \Exception('Invalid access token provided');
			return false;
		} 
		else {
			$response = json_decode($response, true);
			//if (isset($response['description'])) error_log("Request was successful: {$response['description']}\n");
			$response = $response['result'];
		}

		return $response;
	}
	
}