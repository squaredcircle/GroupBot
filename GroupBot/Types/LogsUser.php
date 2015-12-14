<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 19/11/2015
 * Time: 11:08 AM
 */

namespace GroupBot\Types;


class LogsUser
{
    public $User;
    public $chat_id;

    public $posts;
    public $posts_today;
    public $lastpost;
    public $lastpost_date;

    public $LogsCommand = array();

    public function __construct($chat_id, $data, $cmdata)
    {
        $this->chat_id = $chat_id;

        $this->User = new User(array(
            'id' => $data['user_id'],
            'first_name' => $data['user_firstname'],
            'last_name' => $data['user_secondname'],
            'username' => $data['username']
        ));

        $this->posts = $data['posts'];
        $this->posts_today = $data['posts_today'];
        $this->lastpost = $data['lastpost'];
        $this->lastpost_date = $data['lastpost_date'];

        if (!empty($cmdata)) {
            foreach ($cmdata as $cmd) {
                $this->LogsCommand[$cmd['command']] = new LogCommand($cmd);
            }
        } else {
            $this->LogsCommand[''] = new LogCommand(array('command' => '', 'uses' => '', 'uses_today' => '', 'last_used' => ''));
        }
    }
}