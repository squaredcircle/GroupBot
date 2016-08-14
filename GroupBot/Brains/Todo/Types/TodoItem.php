<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 20/02/2016
 * Time: 1:54 PM
 */

namespace GroupBot\Brains\Todo\Types;


use GroupBot\Types\User;

class TodoItem
{
    /** @var  int */
    public $id;

    /** @var  string */
    public $description;

    /** @var  User */
    public $owner;

    public function __construct($description, User $owner)
    {
        $this->description = $description;
        $this->owner = $owner;
    }
}