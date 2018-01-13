<?php

/*
Groupbot Commands for Botfather:

help- You're confused.
blackjack- You like to gamble.
vote- You wanna be popular.
level- You want to be powerful.
leaderboard- You want to see your power.
income- You want money.
reload- You're done with it all.
roll- You want a good time.
click- You've simple tastes.
decide- You need help.
minesweeper- You're nostalgic.
rate- You need an expert opinion.
send- You're feeling generous.

 */

namespace GroupBot;

use GroupBot\Database\User;
use GroupBot\Enums\MessageType;
use GroupBot\Libraries\Dictionary;
use GroupBot\Types\Command;
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
        if ($bot == NULL)
            $this->startBot($debug);
    }

    private function startBot($debug)
    {
        if (isset($debug))
            $update = $debug;
        else {
            $content = file_get_contents("php://input");
            $update = json_decode($content, true);
        }

        if (!$update)
            return false;

//        Telegram::talkForced('56390227', print_r($update, true));
//        return true;

        if (isset($update["message"])) {
            $this->processMessage($update['message']);
        } elseif (isset($update["inline_query"])) {
            $this->InlineQuery = new InlineQuery($update["inline_query"]);
            Telegram::answerInlineQuery($this->InlineQuery->id, $this->InlineQuery->results);
        } elseif (isset($update["callback_query"])) {
            $message = $update["callback_query"]["message"];
            $message['from'] = $update["callback_query"]["from"];
            $message['text'] = $update["callback_query"]["data"];
            $message['callback'] = true;
            $this->processMessage($message);
        }

        return true;
    }

    private function processMessage($message_update)
    {
        $this->db = $this->createPDO();
        $this->Message = new Message($message_update, $this->db);

        if ($this->Message->isCommand()) {
            $this->processCommand();
        } else {
            $Talk = new Talk($this->Message, $this->db);
            $Talk->processMessage();
        }

        $this->processStats();
    }

    public function createPDO()
    {
        $pdo = new \PDO('mysql:host=' . BOT_DB_HOST . ';dbname=' . BOT_DB_NAME . ';charset=utf8', BOT_DB_USER, BOT_DB_PASSWORD);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        return $pdo;
    }

    private function duplicateCommand($class)
    {
        if (strcmp($class, "GroupBot\\Command\\c_surrender") === 0) {
            $SQL = new \GroupBot\Brains\Blackjack\SQL($this->db);
            if ($SQL->select_game($this->Message->Chat->id))
                return true;
        } elseif (strcmp($class, "GroupBot\\Command\\b_surrender") === 0) {
            $SQL = new \GroupBot\Brains\Casinowar\SQL($this->db);
            if ($SQL->select_game($this->Message->Chat->id))
                return true;
        }
        return false;
    }

    private function runCommand($cmd)
    {
        $Dictionary = new Dictionary();
        if (in_array($cmd, $Dictionary->spam_commands) && $this->Message->Chat->no_spam_mode) return false;

        foreach ([NULL, 'coin', 'blackjack', 'casinowars', 'level', 'vote', 'russianroulette', 'misc', 'reminder', 'todo b'] as $folder) {
            if (is_null($folder)) {
                $class = "GroupBot\\Command\\$cmd";
                $class_staging = "GroupBot\\CommandStaging\\$cmd";
            } else {
                $class = "GroupBot\\Command\\$folder\\$cmd";
                $class_staging = "GroupBot\\CommandStaging\\$folder\\$cmd";
            }

            if (class_exists($class)) {
                if ($this->duplicateCommand($class)) continue;
                /** @var Command $obj */
                $obj = new $class($this->Message, $this->db);
                $obj->main();
                return true;
            } elseif ($this->Message->User->user_id == '56390227' && class_exists($class_staging)) {
                if ($this->duplicateCommand($class_staging)) continue;
                /** @var Command $obj */
                $obj = new $class_staging($this->Message, $this->db);
                $obj->main();
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
        else {
            $key = array_search($this->Message->command, array_keys($dict->aliases));
            $alias = ($key !== false) ? array_values($dict->aliases)[$key] : false;
            if ($alias !== false)
                return $this->runCommand($alias);
        }
        return false;
    }

    private function processStats()
    {
        $userSQL = new User($this->db);
        if ($this->Message->isNormalMessage()) {
            $userSQL->updateUserMessageStats($this->Message);
        } elseif ($this->Message->MessageType == MessageType::NewChatParticipant) {
            $userSQL->updateWhetherUserIsInChat($this->Message->Chat, $this->Message->User, true);
        } elseif ($this->Message->MessageType == MessageType::LeftChatParticipant) {
            $userSQL->updateWhetherUserIsInChat($this->Message->Chat, $this->Message->User, false);
        } elseif ($this->Message->isCommand()) {
            $userSQL->updateUserCommandStats($this->Message);
        }
    }
}