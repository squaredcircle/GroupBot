<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 6/11/2015
 * Time: 11:00 PM
 */

namespace GroupBot\Types;


class Command
{
    protected $Message;

    public function __construct(Message $message)
    {
        $this->Message = $message;
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

    public function getAllParams()
    {
        return $this->Message->text;
    }

}