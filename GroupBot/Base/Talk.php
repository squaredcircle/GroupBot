<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 6/11/2015
 * Time: 12:25 PM
 */

namespace GroupBot\Base;

use GroupBot\Brains\Translate;
use GroupBot\Enums\MessageType;
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

    private function dictMatch($phrases, $exclusions = NULL)
    {
        foreach (array_keys($phrases) as $phrase) {
            if (stripos($this->Message->text, $phrase) !== false)
            {
                if (isset($exclusions) && array_key_exists($phrase, $exclusions))
                {
                    $excluded_phrases = $exclusions[$phrase];
                    if (is_array($excluded_phrases)) {
                        foreach ($excluded_phrases as $exclude) {
                            if (stripos($this->Message->text, $exclude) !== false) {
                                continue 2;
                            }
                        }
                    } else {
                        if (stripos($this->Message->text, $exclusions[$phrase]) !== false) {
                            continue;
                        }
                    }
                }
                $this->Telegram->talk($this->Message->Chat->id, $phrases[$phrase]);
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

    private function processChannelChange()
    {
        require(__DIR__ . '/../libraries/dictionary.php');

        switch ($this->Message->MessageType) {
            case MessageType::NewChatTitle:
                $message = $ratings[mt_rand(0,10)];
                break;
            case MessageType::NewChatPhoto:
                $message = $ratings[mt_rand(0,10)];
                break;
            case MessageType::NewChatParticipant:
                $message = 'hi new guy';
                break;
            case MessageType::LeftChatParticipant:
                $message = 'such is life, brahs';
                break;
            case MessageType::DeleteChatPhoto:
                $message = 'why, fam?';
                break;
        }
        if (isset($message)) $this->Telegram->talk($this->Message->Chat->id, $message);
    }

    public function processMessage()
    {
        require(__DIR__ . '/../libraries/dictionary.php');

        if ($this->dictMatch($dict_interjections, $dict_interjections_exclusions)) return true;
        if (!$this->Message->isNormalMessage()) $this->processChannelChange();

        if ($this->isShitBotMentioned())
        {
            if ($this->dictCommand($dict_reply_commands)) return true;
            if ($this->dictUsers($dict_user_replies)) return true;
            if ($this->dictMatch($dict_replies)) return true;

            if (count(mb_split(" ", $this->Message->text)) < 6) {
                $this->Telegram->reply($this->Message->Chat->id, $this->Message->message_id, $dict_default_reply);
                return true;
            }
        }

        if (count(mb_split(" ", $this->Message->text)) > 2) {
            $Translate = new Translate();
            $lang = $Translate->detectLanguage($this->Message->text);
            if ($lang != 'English') {
                $translation = $Translate->translate($this->Message->text, 'English');
                $this->Telegram->talk($this->Message->Chat->id, "_(" . $translation['lang_source'] . ")_* " . $translation['result'][0] . "*");
            }
        }
        return true;
    }
}