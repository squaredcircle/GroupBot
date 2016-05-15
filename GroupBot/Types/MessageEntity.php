<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 14/05/2016
 * Time: 12:47 AM
 */

namespace GroupBot\Types;


use GroupBot\Enums\MessageEntityType;

class MessageEntity
{
    /** @var  MessageEntityType */
    public $type;

    /** @var  integer */
    public $offset;

    /** @var  integer */
    public $length;

    /** @var  string */
    public $url;

    public function __construct($type, $offset, $length, $url = NULL)
    {
        $this->type = $this->determineType($type);
        $this->offset = $offset;
        $this->length = $length;
        $this->url = $url;
    }

    private function determineType($type)
    {
        switch ($type) {
            case 'mention':
                return new MessageEntityType(MessageEntityType::mention);
                break;
            case 'hashtag':
                return new MessageEntityType(MessageEntityType::hashtag);
                break;
            case 'bot_command':
                return new MessageEntityType(MessageEntityType::bot_command);
                break;
            case 'url':
                return new MessageEntityType(MessageEntityType::url);
                break;
            case 'email':
                return new MessageEntityType(MessageEntityType::email);
                break;
            case 'bold':
                return new MessageEntityType(MessageEntityType::bold);
                break;
            case 'italic':
                return new MessageEntityType(MessageEntityType::italic);
                break;
            case 'code':
                return new MessageEntityType(MessageEntityType::code);
                break;
            case 'pre':
                return new MessageEntityType(MessageEntityType::pre);
                break;
            case 'text_link':
                return new MessageEntityType(MessageEntityType::text_link);
                break;
        }
        return NULL;
    }
}