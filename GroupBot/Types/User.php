<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 15/11/2015
 * Time: 6:46 PM
 */

namespace GroupBot\Types;


class User
{
    public $id;
    public $user_name;
    public $first_name;
    public $last_name;

    public function __construct($user)
    {
        $this->id = $user['id'];
        $this->user_name = isset($user['username']) ? $user['username'] : NULL;
        $this->last_name = isset($user['last_name']) ? $user['last_name'] : NULL;
        $this->first_name = $user['first_name'];
    }

    public function hasUserName()
    {
        return isset($this->user_name);
    }

    private function hasFirstName()
    {
        return isset($this->first_name);
    }

    public function hasLastName()
    {
        return isset($this->last_name);
    }

    public function hasFullName()
    {
        return ($this->hasFirstName() && $this->hasLastName());
    }
}