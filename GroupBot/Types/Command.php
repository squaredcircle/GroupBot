<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 6/11/2015
 * Time: 11:00 PM
 */

namespace GroupBot\Types;


abstract class Command
{
    /** @var Message  */
    protected $Message;

    /** @var \PDO  */
    protected $db;

    public function __construct(Message $message, \PDO $db)
    {
        $this->Message = $message;
        $this->db = $db;
    }

    public function isParam()
    {
        return !empty($this->Message->text);
    }

    public function noParams()
    {
        return count(explode(' ', $this->Message->text));
    }

    /**
     * @param int $no Indexed from 0
     * @return mixed
     */
    public function getParam($no = 0)
    {
        return explode(' ', $this->Message->text)[$no];
    }

    public function getParamsAsArray()
    {
        return explode(' ', $this->Message->text);
    }

    public function getAllParams()
    {
        return $this->Message->text;
    }

    abstract public function main();

}