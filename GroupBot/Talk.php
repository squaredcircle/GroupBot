<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 6/11/2015
 * Time: 12:25 PM
 */

namespace GroupBot;

use GroupBot\Brains\Translate;
use GroupBot\Enums\MessageType;
use GroupBot\Libraries\Dictionary;
use GroupBot\Types\Message;

class Talk
{
    /** @var Message  */
    private $Message;

    /** @var Dictionary  */
    private $dict;

    public function __construct(Message $message)
    {
        $this->Message  = $message;
        $this->dict = new Dictionary();
    }

    private function isBotMentioned()
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
                Telegram::talk($this->Message->Chat->id, $phrases[$phrase]);
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
               Telegram::reply($this->Message->Chat->id, $this->Message->message_id, $phrases[$i]);
               return true;
           }
        }
        return false;
    }

    private function processChannelChange()
    {
        switch ($this->Message->MessageType) {
            case MessageType::NewChatTitle:
                $message = $this->dict->ratings[mt_rand(0,10)];
                break;
            case MessageType::NewChatPhoto:
                $message = $this->dict->ratings[mt_rand(0,10)];
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
        if (isset($message)) Telegram::talk($this->Message->Chat->id, $message);
    }

    public function processMessage()
    {
        if ($this->dictMatch($this->dict->interjections, $this->dict->interjections_exclusions)) return true;
        if (!$this->Message->isNormalMessage()) $this->processChannelChange();

        if ($this->isBotMentioned())
        {
            if ($this->dictCommand($this->dict->reply_commands)) return true;
            if ($this->dictUsers($this->dict->user_replies)) return true;
            if ($this->dictMatch($this->dict->replies)) return true;

            if (count(mb_split(" ", $this->Message->text)) < 6) {
                Telegram::reply($this->Message->Chat->id, $this->Message->message_id, $this->dict->default_reply);
                return true;
            }
        }

        if (count(mb_split(" ", $this->Message->text)) > 3) {
            $Translate = new Translate();
            $lang = $Translate->detectLanguage($this->Message->text);
            if ($lang != 'English') {
                $translation = $Translate->translate($this->Message->text, 'English');
                Telegram::talk($this->Message->Chat->id, "_(" . $translation['lang_source'] . ")_* " . $translation['result'][0] . "*");
            }
        }
        return true;
    }
}