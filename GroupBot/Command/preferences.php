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

    /** @var  \GroupBot\Types\Chat */
    private $chat;

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
            if (isset($keyboard))
                $this->keyboard[] = $keyboard;

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
            . "\n`   `• Bot kick mode is " . ($this->Message->Chat->bot_kick_mode ? "*on*" : "*off*")
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

    public function chat_options($updated = false)
    {
        if ($updated) {
            $this->out = emoji(0x2714) . " Preference updated! \n\n";
        } else {
            $this->out = '';
        }

        $DbUser = new \GroupBot\Database\User($this->db);
        $admin = $DbUser->getUserFromId($this->chat->admin_user_id);
        $this->out .=
            emoji(0x2699) . " Current preferences for `" . $this->chat->title . "`:"
            . "\n"
            . "\n`   `• My admin for this chat is " . ($admin ? "*" . $admin->getName() . "*" : "_nobody?_")
            . "\n`   `• Reduced spam mode is " . ($this->chat->no_spam_mode ? "*on*" : "*off*")
            . "\n`   `• Bot kick mode is " . ($this->chat->bot_kick_mode ? "*on*" : "*off*")
            . "\n`   `• Yandex auto-translate is " . ($this->chat->yandex_enabled ? "*on*" : "*off*");

        if ($this->chat->yandex_enabled) {
            $this->out .= "\n`   `• It will translate messages of at least *" . $this->chat->yandex_min_words . "* foreign words to *" . $this->chat->yandex_language . "*";
        }

        $this->out .= "\n\nYou can change these settings below.";

        $this->keyboard = [];
        $this->keyboard[] =
            [
                [
                    'text' => emoji($this->chat->no_spam_mode ? 0x1F534 : 0x1F535) . ' Turn reduced spam mode ' . ($this->chat->no_spam_mode ? 'OFF' : 'ON'),
                    'callback_data' => '/preferences chatset ' . $this->chat->id . ' no_spam_mode ' . ($this->chat->no_spam_mode ? '0' : '1')
                ]
            ];
        $this->keyboard[] =
            [
                [
                    'text' => emoji($this->chat->bot_kick_mode ? 0x1F534 : 0x1F535) . ' Turn bot kicking mode ' . ($this->chat->bot_kick_mode ? 'OFF' : 'ON'),
                    'callback_data' => '/preferences chatset ' . $this->chat->id . ' bot_kick_mode ' . ($this->chat->bot_kick_mode ? '0' : '1')
                ]
            ];
        if ($this->chat->yandex_enabled) {
            $this->keyboard[] =
                [
                    [
                        'text' => emoji(0x1F534) . ' Turn Yandex auto-translate OFF',
                        'callback_data' => '/preferences chatset ' . $this->chat->id . ' yandex_enabled 0'
                    ]
                ];
            $this->keyboard[] =
                [
                    [
                        'text' => 'Change Yandex settings',
                        'callback_data' => '/preferences yandex ' . $this->chat->id
                    ]
                ];
        } else {
            $this->keyboard[] =
                [
                    [
                        'text' => emoji(0x1F535) . ' Turn Yandex auto-translate ON',
                        'callback_data' => '/preferences chatset ' . $this->chat->id . ' yandex_enabled 1'
                    ]
                ];
        }
        $this->keyboard[] =
            [
                [
                    'text' => emoji(0x2699) . ' Back to preferences',
                    'callback_data' => '/preferences'
                ],
                [
                    'text' => emoji(0x1F6AA) . ' Back to main menu',
                    'callback_data' => '/help'
                ]
            ];
        return true;
    }

    private function set_boolean_option($parameter, $value)
    {
        if (strcmp($value, '0') === 0) {
            $this->chat->$parameter = false;
            return $this->chat->save($this->db);
        } elseif (strcmp($value, '1') === 0) {
            $this->chat->$parameter = true;
            return $this->chat->save($this->db);
        }
        return false;
    }

    public function set_option($parameter, $value)
    {
        $DbUser = new \GroupBot\Database\User($this->db);
        $admin = $DbUser->getUserFromId($this->chat->admin_user_id);

        if ($admin->user_id == $this->Message->User->user_id) {
            switch ($parameter) {
                case 'no_spam_mode':
                    return $this->set_boolean_option('no_spam_mode', $value);
                    break;
                case 'bot_kick_mode':
                    return $this->set_boolean_option('bot_kick_mode', $value);
                    break;
                case 'yandex_enabled':
                    return $this->set_boolean_option('yandex_enabled', $value);
                    break;
            }
        }
        return false;
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
                case 'chat':
                    if ($this->noParams() == 2) {
                        $DbChat = new \GroupBot\Database\Chat($this->db);
                        if ($this->chat = $DbChat->getChatById($this->getParam(1))) {
                            $this->chat_options(false);
                            break;
                        }
                    }
                    $this->all_options();
                    break;
                case 'chatset':
                    if ($this->noParams() == 4) {
                        $DbChat = new \GroupBot\Database\Chat($this->db);
                        if ($this->chat = $DbChat->getChatById($this->getParam(1))) {
                            if ($this->set_option($this->getParam(2), $this->getParam(3))) {
                                $this->chat_options(true);
                            } else {
                                $this->chat_options(false);
                            }
                            break;
                        }
                    }
                    $this->all_options();
                    break;
                case 'yandex':

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