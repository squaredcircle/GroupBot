<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 19/11/2015
 * Time: 11:08 AM
 */

namespace GroupBot\Types;


class UserPostStats
{
    /** @var  User */
    public $User;

    /** @var  Chat */
    public $Chat;

    /** @var  int */
    public $posts;

    /** @var  int */
    public $posts_today;

    /** @var  string */
    public $lastpost;

    public $lastpost_date;
}