<?php

namespace GroupBot\Base;

use GroupBot\Brains\Coin\Coin;
use GroupBot\Types\InlineQuery;
use GroupBot\Types\Message;

require(__DIR__ . '/../libraries/common.php');
require(__DIR__ . '/../Settings.php');

class GroupBot
{
	public $Message;
	public $InlineQuery;

	public function __construct($debug = NULL, $bot = NULL)
	{
		if ($bot == NULL) $this->startBot($debug);
	}

	private function startBot($debug)
	{
		if (isset($debug))
		{
			$update = $debug;
		}
		else
		{
			$content = file_get_contents("php://input");
			$update = json_decode($content, true);
		}

		if (!$update) return 0;		// wrong update

		if (isset($update["message"])) {
			$this->Message = new Message($update['message']);

			if ($this->Message->isCommand()) {
				$this->processCommand();
			} else {
				$Talk = new Talk($this->Message);
				$Talk->processMessage();
			}

			$Logging = new Logging($this->Message);
			$Logging->doUpdates();


			$Coin = new Coin();
			if ($Coin->checkForAndCreateUser($this->Message->User)) {
				$Telegram = new Telegram();
				$Telegram->talk($this->Message->Chat->id, "Hi " . $this->Message->User->first_name . "! Your " . COIN_CURRENCY_NAME
					. " has been set up; you've got 0 " . COIN_CURRENCY_NAME . " at the moment.");
			}
		} elseif (isset($update["inline_query"])) {
			$this->InlineQuery = new InlineQuery($update["inline_query"]);
			$Telegram = new Telegram();
			$Telegram->answerInlineQuery($this->InlineQuery->id, $this->InlineQuery->results);
		}

		return true;
	}

	private function runCommand($cmd)
	{
		foreach(['t_', 'i_', 's_', 'q_', 'b_'] as $i)
		{
			$command = $i . $cmd;
			$class = "GroupBot\\Command\\" . $command;

			if (class_exists($class))
			{
				try {
                    $obj = new $class($this->Message);
                    $obj->$command();
                } catch (\Exception $e) {
                    $Telegram = new Telegram();
                    $Telegram->talk($this->Message->Chat->id, "something's broken inside, brah");
                    return false;
                }
				return true;
			}
		}
		return false;
	}

	private function processCommand()
	{
		require(__DIR__ . '/../libraries/aliases.php');

		if ($this->runCommand($this->Message->command))
			return true;
		else
		{
			$alias = checkAlias($this->Message->command);
			if ($alias !== false) $this->runCommand($alias);
		}
			return false;
	}
}