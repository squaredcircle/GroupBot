<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 19/11/2015
 * Time: 11:12 AM
 */

namespace GroupBot\Types;


class LogCommand
{
    public $command;
    public $uses;
    public $uses_today;
    public $last_used;

    public function __construct($data)
    {
        $this->command = $data['command'];
        $this->uses = $data['uses'];
        $this->uses_today = $data['uses_today'];
        $this->last_used = $data['last_used'];
    }
}