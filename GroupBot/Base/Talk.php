<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 6/11/2015
 * Time: 12:25 PM
 */

namespace GroupBot\Base;

use GroupBot\Types\Message;

class Talk
{
    private $Telegram;
    private $Message;

    public function __construct(Message $message)
    {
        $this->Telegram = new Telegram();
        $this->Message  = $message;
    }

    private function isShitBotMentioned()
    {
        return isset($this->Message->text) && (stripos($this->Message->text, BOT_FRIENDLY_NAME) !== false);
    }

    private function dictMatch($phrases)
    {
        $keys = array_keys($phrases);
        foreach ($keys as $i) {
            if (stripos($this->Message->text, $i) !== false) {
                $this->Telegram->talk($this->Message->Chat->id, $phrases[$i]);
                return true;
            }
        }
        return false;
    }

    private function dictCommand($commands)
    {
        $keys = array_keys($commands);
        foreach ($keys as $i) {
            if (stripos($this->Message->text, $i) !== false) {

                $command = "t_" . $commands[$i];
                $class = "GroupBot\\Command\\" . $command;

                if (class_exists($class))
                {
                    $obj = new $class($this->Message);
                    $obj->$command();
                    return true;
                }
            }
        }
        return false;
    }

    private function dictUsers($phrases)
    {
        $keys = array_keys($phrases);
        foreach ($keys as $i) {
           if (strcmp($this->Message->User->user_name, $i) == 0) {
               $this->Telegram->reply($this->Message->Chat->id, $this->Message->message_id, $phrases[$i]);
               return true;
           }
        }
        return false;
    }

    public function processMessage()
    {
        require(__DIR__ . '/../libraries/dictionary.php');

        if ($this->dictMatch($dict_interjections)) return true;

        if ($this->isShitBotMentioned())
        {
            if ($this->dictCommand($dict_reply_commands)) return true;
            if ($this->dictUsers($dict_user_replies)) return true;
            if ($this->dictMatch($dict_replies)) return true;

            $this->Telegram->reply($this->Message->Chat->id, $this->Message->message_id, $dict_default_reply);
        }
        return true;
    }
}