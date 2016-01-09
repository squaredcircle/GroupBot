<?php

namespace GroupBot\Base;

require_once(__DIR__ . '/../Settings.php');

class Telegram
{
	public function __construct()
    {

	}

	public function customShitpostingMessage($text)
	{
		$this->talkForced(SHITPOSTING_ID, $text);
	}

	public function CoinBroadcast($text)
	{
		$this->apiRequest("sendMessage", array('chat_id' => '@IsaacCoin', "text" => $text, "parse_mode" => "Markdown"));
	}
	
	public function talk($chat_id, $text)
	{
		$this->apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "text" => $text, "parse_mode" => "Markdown"));
	}

	public function talk_suppress($chat_id, $text)
	{
		$this->apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "text" => $text, "parse_mode" => "Markdown", "disable_web_page_preview" => "true"));
	}

	public function reply_keyboard($chat_id, $text, $message_id, $keyboard)
	{
		$this->apiRequestWebhook("sendMessage",
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

	public function talk_hide_keyboard($chat_id, $text)
	{
		$this->apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "text" => $text, "parse_mode" => "Markdown",
			"reply_markup" => array("hide_keyboard" => true)));
	}

	public function talkForced($chat_id, $text)
	{
		$this->apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $text));
	}

	public function reply($chat_id, $message_id, $text)
	{
		$this->apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "reply_to_message_id" => $message_id, "text" => $text, "parse_mode" => "Markdown"));
	}

	public function answerInlineQuery($inline_query_id, $results)
	{
		$this->apiRequestWebhook("answerInlineQuery", array('inline_query_id' => $inline_query_id, 'cache_time' => 0, 'results' => $results));
	}

	public function apiRequestWebhook($method, $parameters)
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

	public function apiRequest($method, $parameters)
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

		foreach ($parameters as $key => &$val)
		{
			// encoding to JSON array parameters, for example reply_markup
			if (!is_numeric($val) && !is_string($val))
				$val = json_encode($val);
		}
		$url = API_URL.$method.'?'.http_build_query($parameters);

		$handle = curl_init($url);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($handle, CURLOPT_TIMEOUT, 60);

		return $this->exec_curl_request($handle);
	}

	public function imagickPhotoSender($chat_id, $img, $size)
	{
		$bot_url    = "https://api.telegram.org/bot" . BOT_TOKEN;
		$url = $bot_url . "/sendPhoto?chat_id=" . $chat_id;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array("photo"     => $img, ));
		curl_setopt($ch, CURLOPT_INFILESIZE, $size);
		$output = curl_exec($ch);
		print $output;
	}

	public function customPhotoSender($chat_id, $file_path)
	{
		$bot_url    = "https://api.telegram.org/bot" . BOT_TOKEN;
		$url = $bot_url . "/sendPhoto?chat_id=" . $chat_id;
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, array("photo"     => "@" . $file_path, ));
		curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file_path));
		$output = curl_exec($ch);
		print $output;
	}

	public function customPhotoSender2($chat_id, $file_path)
	{
		$bot_url    = "https://api.telegram.org/bot" . BOT_TOKEN;
		$url        = $bot_url . "/sendPhoto?chat_id=" . $chat_id ;

		$post_fields = array('chat_id'   => $chat_id,
				'photo'     => new \CURLFile(realpath($file_path))
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				"Content-Type:multipart/form-data"
		));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		return curl_exec($ch);
		//print_r(json_decode($output,true));
	}

	public function fileIdPhotoSender($chat_id, $file_id)
	{
		$this->apiRequestWebhook("sendPhoto", array('chat_id' => $chat_id, 'photo' => $file_id));
	}

	function exec_curl_request($handle) 
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

		if ($http_code >= 500) 
		{
			// do not wat to DDOS server if something goes wrong
			sleep(10);
			return false;
		} 
		else if ($http_code != 200) 
		{
			$response = json_decode($response, true);
			error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
			if ($http_code == 401) 
			{
				throw new \Exception('Invalid access token provided');
			}
		return false;
		} 
		else 
		{
			$response = json_decode($response, true);
			if (isset($response['description'])) 
			{
				error_log("Request was successful: {$response['description']}\n");
			}
			$response = $response['result'];
		}

		return $response;
	}
	
}