<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:07 AM
 */
namespace GroupBot\Command;

use GroupBot\Command\misc\emoji;
use GroupBot\Database\Chat;
use GroupBot\Telegram;
use GroupBot\Types\Command;
use GroupBot\Types\User;

class preferences extends Command
{
    private $out;
    private $keyboard;

    private function all_options()
    {
        $DbChat = new Chat($this->db);

        $this->out .= emoji(0x2699) . " Hi *" . $this->Message->User->getName() . "*."
            . "\n\nYou can access the following personal preferences:"
            . "\n`   `• Change how your name is displayed";

        $this->keyboard = [
            [
                [
                    'text' => 'Change name',
                    'callback_data' => '/preferences name'
                ]
            ]
        ];

        if ($chats = $DbChat->getChatsByAdmin($this->Message->User->user_id)) {
            $this->out .= "\n"
                . "\nYou're my admin for the following chats:";

            $keyboard = [];
            foreach ($chats as $index => $chat) {
                $this->out .= "\n`   `• `" . $chat->title . "`";
                $keyboard[] = [
                    'text' => $chat->title,
                    'callback_data' => '/preferences chat ' . $chat->id
                ];

                if ($index != 0 && $index % 4 == 0) {
                    $this->keyboard[] = $keyboard;
                    $keyboard = [];
                }
            }
            if (isset($keyboard)) $this->keyboard[] = $keyboard;

            $this->out .= "\n"
                . "\nPlease click on a chat name below to change its settings.";
        }

        $this->keyboard[] =
            [
                [
                    'text' => emoji(0x1F6AA) . ' Back to main menu',
                    'callback_data' => '/help'
                ]
            ];
    }

    private function preferences_updated()
    {
        $this->out .= emoji(0x1F44D) . " Preferences updated!\n\n";
        $this->all_options();
    }

    private function change_name()
    {
        if ($this->noParams() == 2) {
            if (strcmp($this->getParam(1), 'username') === 0) {
                $this->Message->User->handle_preference = 'username';
                $this->Message->User->save($this->db);
                $this->preferences_updated();
            } elseif (strcmp($this->getParam(1), 'fullname') === 0) {
                $this->Message->User->handle_preference = 'fullname';
                $this->Message->User->save($this->db);
                $this->preferences_updated();
            } else {
                $this->all_options();
            }
        } else {
            $pref = (strcmp($this->Message->User->handle_preference, 'username') === 0) ? 'user name' : 'full name';

            $this->out .= emoji(0x2699) . " *Display name*"
                . "\n\nI'm currently showing your *" . $pref . "*."
                . "\n\nChanging this preference affects how I'll address you on (for example) the leaderboard, or in a blackjack game."
                . "\n\nPlease choose an option:"
                . "\n`   `• " . "Display your user name (if you've got one set)"
                . "\n`   `• " . "Display your full name (first and last)";
            $this->keyboard = [
                [
                    [
                        'text' => 'User Name',
                        'callback_data' => '/preferences name username'
                    ],
                    [
                        'text' => 'Full Name',
                        'callback_data' => '/preferences name fullname'
                    ]
                ],
                [
                    [
                        'text' => emoji(0x2699) . ' Back to preferences',
                        'callback_data' => '/preferences'
                    ],
                    [
                        'text' => emoji(0x1F6AA) . ' Back to main menu',
                        'callback_data' => '/help'
                    ]
                ]
            ];
        }
    }

    private function group_status()
    {
        $DbUser = new \GroupBot\Database\User($this->db);
        $admin = $DbUser->getUserFromId($this->Message->Chat->admin_user_id);
        $this->out =
            emoji(0x2699) . " Current preferences for `" . $this->Message->Chat->title . "`:"
            . "\n"
            . "\n`   `• My admin for this chat is " . ($admin ? "*" . $admin->getName() . "*" : "_nobody?_")
            . "\n`   `• Reduced spam mode is " . ($this->Message->Chat->no_spam_mode ? "*on*" : "*off*")
            . "\n`   `• Yandex auto-translate is " . ($this->Message->Chat->yandex_enabled ? "*on*" : "*off*");

        if ($this->Message->Chat->yandex_enabled) {
            $this->out .= "\n`   `• It will translate messages of at least *" . $this->Message->Chat->yandex_min_words . "* foreign words to *" . $this->Message->Chat->yandex_language . "*";
        }

        if ($admin->user_id == $this->Message->User->user_id) {
            $this->out .=
                "\n"
                . "\n" . emoji(0x01F512) . " *" . $this->Message->User->getName() . "*, please talk to me in private if you want to update your these or your own preferences. (Click @" . BOT_FULL_USER_NAME . ")";
        } else {
            $this->out .=
                "\n"
                . "\n" . emoji(0x01F512) . " *" . $this->Message->User->getName() . "*, please talk to me in private if you want to update your personal preferences. (Click @" . BOT_FULL_USER_NAME . ")";
        }
    }

    public function main()
    {
        if (!$this->Message->Chat->isPrivate()) {
            $this->group_status();
            Telegram::talk($this->Message->Chat->id, $this->out);
            return true;
        }

        $this->out = '';

        if ($this->isParam()) {
            switch ($this->getParam()) {
                case 'name':
                    $this->change_name();
                    break;
                default:
                    $this->all_options();
            }
        } else {
            $this->all_options();
        }

        if ($this->Message->isCallback())
            Telegram::edit_inline_message($this->Message->Chat->id, $this->Message->message_id, $this->out, $this->keyboard);
        else Telegram::talk_inline_keyboard($this->Message->Chat->id, $this->out, $this->keyboard);
        return true;
    }
}