<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 19/11/2015
 * Time: 11:12 AM
 */

namespace GroupBot\Types;


class UserCommandStats
{
    /** @var  User */
    public $User;

    /** @var  Chat */
    public $Chat;

    /** @var  string */
    public $command;

    /** @var  int */
    public $uses;

    /** @var  int */
    public $uses_today;

    public $last_used;
}