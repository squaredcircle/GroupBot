<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:14 AM
 */
namespace GroupBot\Command\coin;

use GroupBot\Brains\Query;
use GroupBot\Database\Chat;
use GroupBot\Telegram;
use GroupBot\Types\Command;
use GroupBot\Types\User;

class leaderboard extends Command
{
    private $global = false;
    /** @var  \GroupBot\Types\Chat */
    private $chat;

    /**
     * @param $str
     * @param array $arr
     * @return bool
     */
    private function contains($str, array $arr)
    {
        foreach ($arr as $a) {
            if (stripos($a, $str) !== false)
                return true;
        }
        return false;
    }

    private function getChat()
    {
        if ($this->Message->Chat->isPrivate()) {
            $DbChat = new Chat($this->db);
            if ($this->isParam() && $this->chat = $DbChat->getChatById($this->getParam())) {
                return true;
            }
            $this->chat = NULL;
            $this->global = true;
        } else {
            $this->chat = $this->Message->Chat;
        }
        return true;
    }

    /**
     * @return User[]|bool
     */
    private
    function getUsers()
    {
        $ascending = false;
        $no_users = 10;

        if ($this->isParam()) {
            if ($this->contains($this->getAllParams(), ['global', 'all', 'every'])) {
                $this->chat = NULL;
                $this->global = true;
            }
            if ($this->contains($this->getAllParams(), ['bottom', 'last'])) {
                $ascending = true;
            }
        }
        return Query::getUsersByLevel($this->db, $this->chat, true, $ascending, $no_users);
        //return Query::getUsersByMoneyAndLevel($this->db, $this->chat, true, $ascending, $no_users);
    }

    /**
     * @param User[] $users
     * @return string
     */
    private
    function getTextLeaderboard($users)
    {
        $out = "";
        $index = 1;

        foreach ($users as $user) {
            $out .= "`" . addOrdinalNumberSuffix($index);
            if ($index == 10) {
                $out .= " `";
            } else {
                $out .= "  `";
            }
            if ($user->user_id == COIN_BANK_ID)
                $user->level = 99;
            $out .= "_Lvl " . $user->level;
            if ($user->level >= 10) {
                $out .= " _ * ";
            } else {
                $out .= "   _ * ";
            }

            $out .= $user->getName() . "* (" . $user->getBalance() . ")\n";
            $index++;
            if ($index > 10)
                break;
        }

        return $out;
    }

    private function keyboard()
    {
        $DbUser = new \GroupBot\Database\User($this->db);
        $chats = $DbUser->getActiveChatsByUser($this->Message->User);
        $keyboard =
            [
                [
                    [
                        'text' => 'Global',
                        'callback_data' => '/leaderboard global'
                    ]
                ],
                [
                    [
                        'text' => emoji(0x1F4BC) . ' Back to business menu',
                        'callback_data' => '/help business'
                    ],
                    [
                        'text' => emoji(0x1F6AA) . ' Back to main menu',
                        'callback_data' => '/help'
                    ]
                ]
            ];
        $index = 0;
        foreach ($chats as $chat) {
            if ($index++ > 3)
                break;
            $keyboard[0][] = [
                'text' => $chat->title,
                'callback_data' => '/leaderboard ' . $chat->id
            ];
        }
        return $keyboard;
    }

    public function main()
    {
        $this->getChat();

        if ($users = $this->getUsers()) {
            if ($this->global) {
                $out = "*Global* leaderboard:\n";
                $out .= $this->getTextLeaderboard($users);
            } else {
                $out = "Leaderboard for *" . $this->chat->title . "*:\n";
                $out .= $this->getTextLeaderboard($users);
            }
        } else {
            $out = "Can't find any users, brah";
        }

        if ($this->Message->Chat->isPrivate()) {
            $out .= "\nYou can also view these recent group chat leaderboards:";
            if ($this->Message->isCallback())
                Telegram::edit_inline_message($this->Message->Chat->id, $this->Message->message_id, $out, $this->keyboard());
            else Telegram::talk_inline_keyboard($this->Message->Chat->id, $out, $this->keyboard());
        } else {
            Telegram::talk($this->Message->Chat->id, $out);
        }
    }
}