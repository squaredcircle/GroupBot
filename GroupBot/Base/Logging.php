<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/11/2015
 * Time: 11:57 PM
 */

namespace GroupBot\Base;


use GroupBot\Types\LogsUser;
use GroupBot\Types\Message;

class Logging
{
    private $Message;
    private $DbControl;

    public function __construct(Message $message)
    {
        $this->Message = $message;
        $this->DbControl = new DbControl();
    }

    public function doUpdates()
    {
        $this->updatePosts();
        if ($this->Message->isCommand()) $this->updateCommands();
    }

    private function updatePosts()
    {
        if ($this->Message->isNormalMessage()) {
            $this->DbControl->updatePostLogs($this->Message->Chat->id, $this->Message->User, $this->Message->raw_text);
        }
    }

    private function updateCommands()
    {
        $this->DbControl->updateCommandLogs($this->Message->command, $this->Message->Chat->id, $this->Message->User->id);
    }

    public function checkIfUserIsLogged($user_str)
    {
        return $this->DbControl->isUserLogged($user_str, $this->Message->Chat->id);
    }

    public function getAllUserLogsForChat($user_id)
    {
        $posts = $this->DbControl->getUserPostLogsInChat($this->Message->Chat->id, $user_id);
        $commands = $this->DbControl->getUserCommandLogsInChat($this->Message->Chat->id, $user_id);

        return new LogsUser($this->Message->Chat->id, $posts, $commands);
    }
}