<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 6/11/2015
 * Time: 11:00 PM
 */

namespace GroupBot\Types;


use GroupBot\Base\Telegram;


class Command
{
    protected $Telegram, $Message;

    public function __construct(Message $message)
    {
        $this->Message = $message;
        $this->Telegram = new Telegram();
    }

    public function isParam()
    {
        return !empty($this->Message->text);
    }

    public function noParams()
    {
        return count(explode(' ', $this->Message->text));
    }

    public function getParam($no = 0)
    {
        return explode(' ', $this->Message->text)[$no];
    }

}