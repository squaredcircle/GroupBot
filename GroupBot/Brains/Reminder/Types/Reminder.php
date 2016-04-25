<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 24/04/2016
 * Time: 9:27 PM
 */

namespace GroupBot\Brains\Reminder\Types;


class Reminder
{
    public $id;
    public $user_id;
    public $chat_id;
    public $date_created;
    public $date_due;
    public $content;

    public function construct($user_id, $chat_id, $date_created, $date_due, $content)
    {
        $this->user_id = $user_id;
        $this->chat_id = $chat_id;
        $this->date_created = $date_created;
        $this->date_due = $date_due;
        $this->content = $content;
    }
}