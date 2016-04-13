<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:07 AM
 */
namespace GroupBot\Command;

use GroupBot\Telegram;
use GroupBot\Types\Command;
use GroupBot\Types\User;

class preferences extends Command
{
    private $out;
    private $keyboard;

    private function all_options()
    {
        $this->out .= emoji(0x2699) . " Hi *" . $this->Message->User->getName() . "*."
            . "\n\nYou can access the following preferences:"
            . "\n`   `• Change how your name is displayed";
        $this->keyboard = [
            [
                [
                    'text' => 'Change name',
                    'callback_data' => '/preferences name'
                ]
            ],
            [
                [
                    'text' => emoji(0x1F6AA) . ' Back to main menu',
                    'callback_data' => '/help'
                ],
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

    public function main()
    {
        if (!$this->Message->Chat->isPrivate()) {
            Telegram::talk($this->Message->Chat->id, emoji(0x01F512) . " *" . $this->Message->User->getName() . "*, please talk to me in private if you want to update your preferences. (Click @" . BOT_FULL_USER_NAME . ")");
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