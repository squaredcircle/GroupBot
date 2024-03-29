<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 6/11/2015
 * Time: 10:59 PM
 */

namespace GroupBot\Types;

use GroupBot\Enums\MessageContent;
use GroupBot\Enums\MessageType;

class Message
{
    public $message_id, $date, $callback;
    public $forward_date;

    /** @var  Message */
    public $reply_to_message;

    /** @var  User */
    public $forward_from;

    /** @var  User */
    public $new_chat_participant;

    /** @var  User */
    public $left_chat_participant;

    public $new_chat_title;
    public $new_chat_photo;

    public $command = false;
    public $text;
    public $raw_text;

    /** @var  MessageContent */
    public $MessageContent;

    /** @var  MessageType */
    public $MessageType;

    /** @var  MessageEntity[] */
    public $MessageEntities;

    /** @var User  */
    public $User;

    /** @var Chat  */
    public $Chat;

    private $Audio, $Document, $Photo, $Sticker, $Video, $Voice, $Contact, $Location;

    /** @var  \PDO */
    private $db;

    public function __construct($message, \PDO $db)
    {
        $this->db = $db;
        $this->User = User::constructFromTelegramUpdate($message['from'], $this->db);
        $this->Chat = Chat::constructFromTelegramUpdate($message['chat'], $this->db);

        $this->message_id = $message['message_id'];
        if (isset($message['callback'])) $this->callback = $message['callback'];

        $this->determineMessageContent($message);
        $this->determineMessageType($message);
        $this->determineMessageEntities($message);

        if ($this->MessageContent == MessageContent::Text)
            $this->parseMessage($message);
    }

    public function isText()
    {
        return $this->command == false;
    }

    public function isCommand()
    {
        return $this->command != false;
    }

    public function isNormalMessage()
    {
        return ( $this->MessageType == MessageType::Regular || $this->MessageType == MessageType::Forward
            || $this->MessageType == MessageType::Reply);
    }

    public function isCallback()
    {
        return isset($this->callback);
    }

    public function isLocation()
    {
        return ($this->MessageContent == MessageContent::Location);
    }

    public function Content()
    {
        switch ($this->MessageContent) {
            case (MessageContent::Text):
                return $this->text;
                break;
            case (MessageContent::Audio):
                return $this->Audio;
                break;
            case (MessageContent::Document):
                return $this->Document;
                break;
            case (MessageContent::Photo):
                return $this->Photo;
                break;
            case (MessageContent::Sticker):
                return $this->Sticker;
                break;
            case (MessageContent::Video):
                return $this->Video;
                break;
            case (MessageContent::Voice):
                return $this->Voice;
                break;
            case (MessageContent::Contact):
                return $this->Contact;
                break;
            case (MessageContent::Location):
                return $this->Location;
        }

        return true;
    }

    private function determineMessageEntities($message)
    {
        if (isset($message['entities'])) {
            foreach ($message['entities'] as $entity) {
                $this->MessageEntities[] = new MessageEntity($entity['type'], $entity['offset'], $entity['length'], isset($entity['url']) ? $entity['url'] : NULL);
            }
        }
    }

    private function determineMessageContent($message)
    {
        if (isset($message['audio'])) {
            $this->MessageContent = MessageContent::Audio;
        } elseif (isset($message['document'])) {
            $this->MessageContent = MessageContent::Document;
        } elseif (isset($message['photo'])) {
            $this->MessageContent = MessageContent::Photo;
        } elseif (isset($message['sticker'])) {
            $this->MessageContent = MessageContent::Sticker;
        } elseif (isset($message['video'])) {
            $this->MessageContent = MessageContent::Video;
        } elseif (isset($message['voice'])) {
            $this->MessageContent = MessageContent::Voice;
        } elseif (isset($message['contact'])) {
            $this->MessageContent = MessageContent::Contact;
        } elseif (isset($message['location'])) {
            $this->MessageContent = MessageContent::Location;
            $loc = new Location();
            $loc->latitude = $message['location']['latitude'];
            $loc->longitude = $message['location']['longitude'];
            $this->Location = $loc->getSQLString();
        } elseif (isset($message['text'])) {
            $this->MessageContent = MessageContent::Text;
        } else {
            $this->MessageContent = MessageContent::Unknown;
        }
    }

    private function determineMessageType($message)
    {
        if (isset($message['reply_to_message'])) {
            $this->MessageType = MessageType::Reply;
            $this->reply_to_message = new Message($message['reply_to_message'], $this->db);
        } elseif (isset($message['forward_from'])) {
            $this->forward_from = new User();
            $this->forward_from->constructFromTelegramUpdate($message['forward_from'], $this->db);
            $this->MessageType = MessageType::Forward;
        } elseif (isset($message['new_chat_participant'])) {
            $this->MessageType = MessageType::NewChatParticipant;
            $this->new_chat_participant = User::constructFromTelegramUpdate($message['new_chat_participant'], $this->db);
        } elseif (isset($message['left_chat_participant'])) {
            $this->MessageType = MessageType::LeftChatParticipant;
        } elseif (isset($message['new_chat_title'])) {
            $this->MessageType = MessageType::NewChatTitle;
            $this->new_chat_title = $message['new_chat_title'];
        } elseif (isset($message['new_chat_photo'])) {
            $this->MessageType = MessageType::NewChatPhoto;
            $this->new_chat_photo = $message['new_chat_photo'];
        } elseif (isset($message['delete_chat_photo'])) {
            $this->MessageType = MessageType::DeleteChatPhoto;
        } elseif (isset($message['group_chat_created'])) {
            $this->MessageType = MessageType::GroupChatCreated;
        } elseif (isset($message['supergroup_chat_created'])) {
            $this->MessageType = MessageType::SuperGroupChatCreated;
        } elseif (isset($message['channel_chat_created'])) {
            $this->MessageType = MessageType::ChannelChatCreated;
        } else {
            $this->MessageType = MessageType::Regular;
        }
    }

    private function parseMessage($message)
    {
        $this->raw_text = $message['text'];
        if ($message['text'][0] == '/') {
            $command = substr($message['text'], 1);
            $command = explode(' ', $command)[0];
            if (strpos($command, '@' . BOT_FULL_USER_NAME) !== false)
                $command = str_replace("@" . BOT_FULL_USER_NAME, "", $command);
            $this->command = $command;

            $this->text = substr(strstr($message['text'], ' '), 1);
        } else {
            $this->text = $message['text'];
        }
    }
}
