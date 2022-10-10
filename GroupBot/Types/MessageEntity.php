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
                return MessageEntityType::mention;
                break;
            case 'hashtag':
                return MessageEntityType::hashtag;
                break;
            case 'bot_command':
                return MessageEntityType::bot_command;
                break;
            case 'url':
                return MessageEntityType::url;
                break;
            case 'email':
                return MessageEntityType::email;
                break;
            case 'bold':
                return MessageEntityType::bold;
                break;
            case 'italic':
                return MessageEntityType::italic;
                break;
            case 'code':
                return MessageEntityType::code;
                break;
            case 'pre':
                return MessageEntityType::pre;
                break;
            case 'text_link':
                return MessageEntityType::text_link;
                break;
        }
        return NULL;
    }
}