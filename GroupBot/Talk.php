<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 6/11/2015
 * Time: 12:25 PM
 */

namespace GroupBot;

use GroupBot\Brains\Translate;
use GroupBot\Database\User;
use GroupBot\Enums\MessageEntityType;
use GroupBot\Enums\MessageType;
use GroupBot\Libraries\Dictionary;
use GroupBot\Types\Command;
use GroupBot\Types\Message;

class Talk
{
    /** @var Message  */
    private $Message;

    /** @var Dictionary  */
    private $dict;

    /** @var \PDO  */
    private $db;

    public function __construct(Message $message, \PDO $db)
    {
        $this->Message  = $message;
        $this->db = $db;
        $this->dict = new Dictionary();
    }

    private function isBotMentioned()
    {
        return isset($this->Message->text) && (stripos($this->Message->text, BOT_FRIENDLY_NAME) !== false);
    }

    private function dictMatch($phrases, $exclusions = NULL, $is_command = false)
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
                if ($is_command) {
                    $class = "GroupBot\\Command\\" . $phrases[$phrase];
                    /** @var Command $obj */
                    $obj = new $class($this->Message, $this->db);
                    $obj->main();
                } else {
                    Telegram::talk($this->Message->Chat->id, $phrases[$phrase]);
                }
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

                $class = "GroupBot\\Command\\" . $commands[$i];

                if (class_exists($class)) {
                    /** @var Command $obj */
                    $obj = new $class($this->Message, $this->db);
                    $obj->main();
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
                if (strcasecmp(substr($this->Message->new_chat_participant->user_name,-3), 'bot') === 0 &&
                    strcmp($this->Message->new_chat_participant->user_name, BOT_FULL_USER_NAME) != 0) {
                    if ($this->Message->Chat->bot_kick_mode) {
                        Telegram::kick($this->Message->Chat->id, $this->Message->new_chat_participant->user_id);
                        $message = emoji(0x26A0) . " *Bot detected!*\n\n`Bot kicking mode is online. Kicking bot.`";
                    } else {
                        $message = emoji(0x26A0) . " *Bot detected!*";
                    }
                }
                elseif (strcmp($this->Message->new_chat_participant->user_name, BOT_FULL_USER_NAME) === 0) {
                    $message = $this->dict->join_chat;
                    $this->Message->Chat->admin_user_id = $this->Message->User->user_id;
                    $this->Message->Chat->save($this->db);
                } else {
                    $message = $this->dict->new_chat_member;
                }
                break;
            case MessageType::GroupChatCreated:
                $message = $this->dict->join_chat;
                $this->Message->Chat->admin_user_id = $this->Message->User->user_id;
                $this->Message->Chat->save($this->db);
                break;
            case MessageType::LeftChatParticipant:
                $message = $this->dict->chat_member_left;
                break;
            case MessageType::DeleteChatPhoto:
                $message = $this->dict->chat_photo_deleted;
                break;
        }
        if (isset($message)) Telegram::talk($this->Message->Chat->id, $message);
    }

    private function validateText(Translate $translate, $text)
    {
        return
            (
                (
                    count(mb_split(" ", $text)) > 3
                    && strlen(array_reduce(str_word_count($text, 1), function ($v, $p) {
                        return strlen($v) > strlen($p) ? $v : $p;
                    })) > 1 // Longest word must be over 1 char long
                )
                || $translate->isJapanese($text)
            );
    }

    private function translate()
    {
        if (!$this->Message->Chat->yandex_enabled) return false;
        if ($this->Message->MessageType == MessageType::Forward && strcmp($this->Message->forward_from->user_name, BOT_FULL_USER_NAME) === 0) return false;
        if (isset($this->Message->MessageEntities)) {
            $bold = false;
            $italic = false;
            foreach ($this->Message->MessageEntities as $entity) {
                if ($entity->type == MessageEntityType::bold) $bold = true;
                if ($entity->type == MessageEntityType::italic) $italic = true;
            }
            if ($bold && $italic) return false;
        }
        $Translate = new Translate();
        if ($this->validateText($Translate, $this->Message->text)) {
            $lang = $Translate->detectLanguage($this->Message->text);
            if ($lang != 'English') {
                $translation = $Translate->translate($this->Message->text, 'English');
                Telegram::talk($this->Message->Chat->id, "_(" . $translation['lang_source'] . ")_* " . $translation['result'][0] . "*");
            }
        }
        return true;
    }

    private function greetUser()
    {
        if ($this->Message->Chat->no_spam_mode) return false;
        if ($this->Message->User->welcome_sent) return false;
        Telegram::talk($this->Message->Chat->id,
            emoji(0x1F4EF) . " Arise, *" . $this->Message->User->getName(). "*."
            . "\n\nYou have risen from squalor to become a " . $this->Message->User->getLevelAndTitle() . "."
            . "\nYou find *" . $this->Message->User->getBalance() . " " . COIN_CURRENCY_NAME . "* in a money bag on your person."
            . "\n\nBest of luck, brave traveller. Use /help to get started.");
        $this->Message->User->welcome_sent = true;
        $this->Message->User->save($this->db);
        return true;
    }

    private function sed()
    {
        $s = explode('/', $this->Message->text);
        if (count($s) == 3 && $s[0] == 's')
        {
            if ($this->Message->MessageType == MessageType::Reply)
            {
                $out = preg_replace("/" . $s[1] . "/", $s[2], $this->Message->reply_to_message->text);
                if (strcmp($out, $this->Message->reply_to_message->text) === 0) return false;
            }
            else
            {
                $DbUser = new User($this->db);
                $last_message = $DbUser->getUserPostStatsInChat($this->Message->Chat, $this->Message->User)->lastpost;
                $out = preg_replace("/" . $s[1] . "/", $s[2], $last_message);
                if (strcmp($out, $last_message) === 0) return false;
            }
            Telegram::talk($this->Message->Chat->id, "*$out*");
            return true;
        }
        return false;
    }

    public function processMessage()
    {
        if ($this->greetUser()) return true;

        if (!$this->Message->Chat->no_spam_mode && $this->dictMatch($this->dict->interjections, $this->dict->interjections_exclusions)) return true;
        if ($this->dictMatch($this->dict->interjection_commands, $this->dict->interjections_exclusions, true)) return true;
        if (!$this->Message->isNormalMessage()) $this->processChannelChange();

        if ($this->sed()) return true;

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

        $this->translate();

        return true;
    }
}