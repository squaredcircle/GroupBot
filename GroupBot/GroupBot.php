<?php

namespace GroupBot;

use GroupBot\Database\User;
use GroupBot\Enums\MessageType;
use GroupBot\Libraries\Dictionary;
use GroupBot\Types\InlineQuery;
use GroupBot\Types\Message;

require(__DIR__ . '/Libraries/common.php');
require(__DIR__ . '/Settings.php');

class GroupBot
{
	/** @var  Message */
	public $Message;

	/** @var  InlineQuery */
	public $InlineQuery;

	/** @var \PDO */
	private $db;

	public function __construct($debug = NULL, $bot = NULL)
	{
		if ($bot == NULL) $this->startBot($debug);
	}

	private function startBot($debug)
	{
		if (isset($debug)) $update = $debug;
		else {
			$content = file_get_contents("php://input");
			$update = json_decode($content, true);
		}

		if (!$update) return false;

		if (isset($update["message"]))
		{
            $this->db = $this->createPDO();
            $this->Message = new Message($update['message'], $this->db);

			if ($this->Message->isCommand()) {
				$this->processCommand();
			} else {
				$Talk = new Talk($this->Message);
				$Talk->processMessage();
			}

            $this->processStats();
		}
		elseif (isset($update["inline_query"]))
		{
			$this->InlineQuery = new InlineQuery($update["inline_query"]);
			Telegram::answerInlineQuery($this->InlineQuery->id, $this->InlineQuery->results);
		}

		return true;
	}

	private function createPDO()
	{
		$pdo = new \PDO('mysql:host=' . BOT_DB_HOST . ';dbname=' . BOT_DB_NAME . ';charset=utf8', BOT_DB_USER, BOT_DB_PASSWORD);
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
		return $pdo;
	}

	private function duplicateCommand($class)
	{
		if (strcmp($class, "GroupBot\\Command\\c_surrender") === 0) {
			$SQL = new \GroupBot\Brains\Blackjack\SQL();
			if ($SQL->select_game($this->Message->Chat->id)) return true;
		} elseif (strcmp($class, "GroupBot\\Command\\b_surrender") === 0) {
			$SQL = new \GroupBot\Brains\Casinowar\SQL();
			if ($SQL->select_game($this->Message->Chat->id)) return true;
		}
		return false;
	}

	private function runCommand($cmd)
	{
		foreach(['t_', 'i_', 's_', 'q_', 'b_', 'c_', 'l_', 'v_'] as $i)
		{
			$command = $i . $cmd;
			$class = "GroupBot\\Command\\" . $command;

			if (class_exists($class))
			{
				if ($this->duplicateCommand($class)) continue;
				//try {
                    $obj = new $class($this->Message, $this->db);
                    $obj->$command();
//                } catch (\Exception $e) {
//					Telegram::talk($this->Message->Chat->id, "something's broken inside, brah");
//                    return false;
//                }
				return true;
			}
		}
		return false;
	}

	private function processCommand()
	{
		$dict = new Dictionary();

		if ($this->runCommand($this->Message->command))
			return true;
		else
		{
			$key = array_search($this->Message->command, array_keys($dict->aliases));
			$alias = ($key !== false) ? array_values($dict->aliases)[$key] : false;
			if ($alias !== false) return $this->runCommand($alias);
		}
		return false;
	}

    private function processStats()
    {
        $userSQL = new User($this->db);
        if ($this->Message->isNormalMessage()) {
            $userSQL->updateUserMessageStats($this->Message);
        }
        elseif ($this->Message->MessageType == MessageType::NewChatParticipant) {
            $userSQL->updateWhetherUserIsInChat($this->Message->Chat, $this->Message->User, true);
        }
        elseif ($this->Message->MessageType == MessageType::LeftChatParticipant) {
            $userSQL->updateWhetherUserIsInChat($this->Message->Chat, $this->Message->User, false);
        }
        elseif ($this->Message->isCommand()) {
            $userSQL->updateUserCommandStats($this->Message);
        }
    }
}