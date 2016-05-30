<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:11 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Query;
use GroupBot\Brains\Vote\Vote;
use GroupBot\Database\User;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class help extends Command
{
    private $out;
    private $keyboard;

    /*
echo - It's like talking to a wall!
check - Except this wall talks back?
link - Shit does this one even work?
spookyecho - Wow guys I dno about this
send - help please
leaderboard - of the incomplete
recent - ly i thought
stats - about things
emoji - can't save this GroupBot
person - who made this one jesus
spookyperson - oh please no
zalgo - you wouldn't
zalgomin - that's nice
zalgomax - rusrs
spookyzalgoperson - pls stop
photo - of what?
isaaccoin - sucks lol
 */

    private function main_menu()
    {
        $DbUser = new User($this->db);
        $group_chats = $DbUser->getActiveChatsByUser($this->Message->User);
        $Vote = new Vote($this->db);
        $popularity = $Vote->getVoteTotalForUser($this->Message->User);
        $ranking = Query::getGlobalRanking($this->db, $this->Message->User);

        $this->out = emoji(0x1F44B) . " Hi *" . $this->Message->User->getName() . "*!"
            . "\nI'm *" . BOT_FRIENDLY_NAME . "*, your _Premier Shitposting Solution_ " . emoji(0x2122) . "."
            . "\n\n" . emoji(0x1F481) . emoji(0x1F3FB) . "You're a " . $this->Message->User->getLevelAndTitle() . " with `" . $this->Message->User->getBalance() . "` Coin."
            . "\nOverall, your popularity is at *$popularity* points, and you're ranked *" . addOrdinalNumberSuffix($ranking) . "* on the global leaderboard.";

        $this->out .= "\n\n*This menu system is currently under development and may be incomplete/broken. You've been warned!*";

        if ($group_chats) {
            $this->out .= "\n\nI see you in the following group chats:";
            foreach ($group_chats as $chat) {
                $this->out .= "\n`   `• `" . $chat->title . "`";
            }
        }

        $this->out .= "\n\nWhat would you like?";
        $this->keyboard = [
            [
                [
                    'text' => emoji(0x2754) . ' Help',
                    'callback_data' => '/help help'
                ],
                [
                    'text' => emoji(0x1F4BC) . ' Business',
                    'callback_data' => '/help business'
                ]
            ],
            [
                [
                    'text' => emoji(0x1F3AE) . ' Play a game',
                    'callback_data' => '/help games'
                ],
                [
                    'text' => emoji(0x2699) . ' Change preferences',
                    'callback_data' => '/preferences'
                ]
            ]
        ];
    }

    private function help()
    {
        $this->out = emoji(0x2754) . ' *Help*'
            . "\n\nI'm a very unfocused bot. Here's some of the things I do:"
            . "\n\n`   `• I maintain an *economy* and *level* system for all users"
            . "\n`   `• I will automatically *translate* any non-english messages"
            . "\n`   `• I let you play a few different *games*"
            . "\n`   `• I let you *vote* users up and down"
            . "\n`   `• I can send you *reminders* whenever you wish"
            . "\n`   `• I host a lot of miscellaneous *commands*"
            . "\n\nI'm written in `PHP` by @richardstallman. Development occurs semi-frequently.";
        $this->keyboard = [
            [
                [
                    'text' => emoji(0x1F4D6) . ' Commands?',
                    'callback_data' => '/help commands'
                ],
                [
                    'text' => emoji(0x1F3AE) . ' Games?',
                    'callback_data' => '/help games_help'
                ],
                [
                    'text' => emoji(0x23F2) . ' Reminders?',
                    'callback_data' => '/help reminders'
                ]
            ],
            [
                [
                    'text' => emoji(0x1F5F3) . ' Votes?',
                    'callback_data' => '/help votes'
                ],
                [
                    'text' => emoji(0x1F4B0) . ' Economy?',
                    'callback_data' => '/help economy'
                ],
                [
                    'text' => emoji(0x1F30E) . ' Translation?',
                    'callback_data' => '/help translation'
                ]
            ],
            [
                [
                    'text' => emoji(0x1F6AA) . ' Main menu',
                    'callback_data' => '/help'
                ]
            ]
        ];
    }

    private function commands()
    {
        $this->out = emoji(0x1F4D6) . ' *Commands*'
            . "\n\nHere are some things you can do:"
            . "\n"
            . "\n`   `• /leaderboard to see where everybody is at"
            . "\n`   `• /level to increase your status"
            . "\n`   `• /income to get some coin right now"
            . "\n"
            . "\n`   `• /roll for a good time"  
            . "\n`   `• /time in case you don't live in my time zone"
            . "\n`   `• /help brings up the main menu"
            . "\n"
            . "\n`   `• /christmas if you're feeling cheery"
            . "\n"
            . "\nThis list doesn't include other specific types of commands, like games or settings.";
        $this->keyboard = [
            [
                [
                    'text' => emoji(0x2754) . ' Back to help menu',
                    'callback_data' => '/help help'
                ],
                [
                    'text' => emoji(0x1F6AA) . ' Main menu',
                    'callback_data' => '/help'
                ]
            ]
        ];
    }

    private function games_help()
    {
        $this->out = emoji(0x1F3AE) . ' *Games Help*'
            . "\n"
            . "\nHere's a quick introduction to the games I've got:"
            . "\n"
            . "\n*Blackjack* is a card game where you try to get close to 21."
            . "\n`   `• You start with two cards"
            . "\n`   `• You can *hit* to get another card or *stand* to stop"
            . "\n`   `• If you get closer to 21 than the dealer, you win."
            . "\n`   `• If you go over 21, you lose."
            . "\n"
            . "\n*Casino War* is a simple card game."
            . "\n`   `• Both you and the dealer both receive a card."
            . "\n`   `• The player with the highest card wins."
            . "\n`   `• If you tie, you can go to *war* or *surrender*."
            . "\n"
            . "\nEvery card game requires a mandatory minimum bet of `1` Coin. If you have no Coins, you will be given up to 10 free bets per day.";
        $this->keyboard = [
            [
                [
                    'text' => emoji(0x1F0CF) . ' Play Blackjack',
                    'callback_data' => '/blackjack'
                ]
            ],
            [
                [
                    'text' => emoji(0x2754) . ' Back to help menu',
                    'callback_data' => '/help help'
                ],
                [
                    'text' => emoji(0x1F6AA) . ' Main menu',
                    'callback_data' => '/help'
                ]
            ]
        ];
    }

    private function games()
    {
        $this->out = emoji(0x1F3AE) . ' *Games*'
            . "\n\nWhat would you like to play? I've got:"
            . "\n\n`   `• *Blackjack*"
            . "\n`   `• *Casino War* (currently offline, sorry fam)";
        $this->keyboard = [
            [
                [
                    'text' => emoji(0x1F0CF) . ' Blackjack',
                    'callback_data' => '/blackjack'
                ]
            ],
            [
                [
                    'text' => emoji(0x2754) . ' How do I play?',
                    'callback_data' => '/help games_help'
                ],
                [
                    'text' => emoji(0x1F6AA) . ' Main menu',
                    'callback_data' => '/help'
                ]
            ]
        ];
    }

    private function group_chat()
    {
        $this->out = emoji(0x1F44B) . " Hi *" . $this->Message->User->getName() . "*!"
            . "\nI'm *" . BOT_FRIENDLY_NAME . "*, your _Premier Shitposting Solution_ " . emoji(0x2122) . "."
            . "\n\nTalk to me in private (@" . BOT_FULL_USER_NAME . ") to get a full guided tour. Otherwise, here's some things you can do:"
            . "\n\n`   `• /vote for people"
            . "\n`   `• /leaderboard to see where everybody is at"
            . "\n`   `• /level to increase your status"
            . "\n`   `• /blackjack to earn coin"
            . "\n`   `• /income to get some coin right now"
            . "\n`   `• /reload if you're feeling dangerous"
            . "\n`   `• /roll for a good time"
            . "\n\nMore to come, m80s. Bitch to @richardstallman if I break.";
        return true;
    }

    private function votes()
    {
        $this->out = emoji(0x1F5F3) . ' *Voting System*'
            . "\n"
            . "\nYou can cast votes for users that I know about. You can vote like so:"
            . "\n"
            . "\n`   `• `/vote richardstallman up`"
            . "\n"
            . "\nIf you want to see the voting leaderboard, you can use `/vote` or click the button below."
            . "\n"
            . "\n`   `• The vote options are *up*, *down* or *neutral*"
            . "\n`   `• You can change your vote at any time."
            . "\n";
        $this->keyboard = [
            [
                [
                    'text' => emoji(0x270F) . ' Voting leaderboards',
                    'callback_data' => '/vote'
                ]
            ],
            [
                [
                    'text' => emoji(0x2754) . ' Back to help menu',
                    'callback_data' => '/help help'
                ],
                [
                    'text' => emoji(0x1F6AA) . ' Main menu',
                    'callback_data' => '/help'
                ]
            ]
        ];
    }

    private function reminders()
    {
        $this->out = emoji(0x23F2) . ' *Reminders*'
            . "\n"
            . "\nI can send you text reminders at any time or date in the future. Here's some examples:"
            . "\n"
            . "\n`   `• `/remind` *+2 hours* `to` *pick up the milk*"
            . "\n`   `• `/remind` *2:25PM 26th April* `to` *look out the window*"
            . "\n`   `• `/remind` *tomorrow 5PM* `to` *go outside*"
            . "\n`   `• `/remind` *monday 2AM* `to` *go to sleep*"
            . "\n"
            . "\nMy timezone is *GMT+8*, and it's now *" . date('g:i A') . "* on the *" . date('jS') . "*.";
        $this->keyboard = [
            [
                [
                    'text' => emoji(0x2754) . ' Back to help menu',
                    'callback_data' => '/help help'
                ],
                [
                    'text' => emoji(0x1F6AA) . ' Main menu',
                    'callback_data' => '/help'
                ]
            ]
        ];
    }

    private function economy()
    {
        $this->out = emoji(0x1F4B0) . ' *Economy*'
            . "\n"
            . "\nEvery user I know has a wallet containing *Coins*."
            . "\n"
            . "\n`   `• You can *gain* Coins through daily /income"
            . "\n`   `• You can *bet* your Coins through games"
            . "\n`   `• There is a *fixed* number of Coins in existence. Any Coins you gain or lose originate from *The Bank*."
            . "\n`   `• You can *send* Coins to other users with /send. There is a transfer tax imposed."
            . "\n`   `• Every day at noon, a *random event* occurs. This could give or take Coin from certain users."
            . "\n"
            . "\nComing sometime - more economy statistics and information.";
        $this->keyboard = [
            [
                [
                    'text' => emoji(0x2754) . ' Back to help menu',
                    'callback_data' => '/help help'
                ],
                [
                    'text' => emoji(0x1F6AA) . ' Main menu',
                    'callback_data' => '/help'
                ]
            ]
        ];
    }

    private function translation()
    {
        $this->out = emoji(0x1F30E) . ' *Translation*'
            . "\n"
            . "\nI use *Yandex* to try translate every message I see."
            . "\n"
            . "\n`   `• If I see a message with *4 or more* words, I ask Yandex to detect its language"
            . "\n`   `• If the message is not in *English*, I automatically post a translation"
            . "\n"
            . "\nYou can turn this feature on or off per chat, and change other settings such as the minimum word count or translation language.";
        $this->keyboard = [
            [
                [
                    'text' => emoji(0x2754) . ' Back to help menu',
                    'callback_data' => '/help help'
                ],
                [
                    'text' => emoji(0x1F6AA) . ' Main menu',
                    'callback_data' => '/help'
                ]
            ]
        ];
    }

    public function business()
    {
        $this->out = emoji(0x1F4BC) . ' *Brass tacks*'
            . "\n\nYou can access the following facilities:"
            . "\n"
            . "\n`   `• *Leaderboards* per chat or globally"
            . "\n`   `• *Voting* system"
            . "\n`   `• *Level* purchases"
            . "\n`   `• Daily *income*"
            . "\n"
            . "\nWhat would you like to do?";
        $this->keyboard = [
            [
                [
                    'text' => emoji(0x1F3C6) . ' Leaderboards',
                    'callback_data' => '/leaderboard'
                ],
                [
                    'text' => emoji(0x270F) . ' Votes',
                    'callback_data' => '/vote'
                ],
                [
                    'text' => emoji(0x1F482) . ' Buy Level',
                    'callback_data' => '/buylevel'
                ],
            ],
            [
                [
                    'text' => emoji(0x1F4B8) . ' Receive Income',
                    'callback_data' => '/income'
                ],
                [
                    'text' => emoji(0x1F6AA) . ' Main menu',
                    'callback_data' => '/help'
                ]
            ]
        ];
    }

    public function main()
    {
        $this->out = '';

        if (!$this->Message->Chat->isPrivate()) {
            $this->group_chat();
            Telegram::talk($this->Message->Chat->id, $this->out);
            return true;
        }

        if ($this->isParam()) {
            switch ($this->getParam()) {
                case 'help':
                    $this->help();
                    break;
                case 'commands':
                    $this->commands();
                    break;
                case 'games':
                    $this->games();
                    break;
                case 'games_help':
                    $this->games_help();
                    break;
                case 'votes':
                    $this->votes();
                    break;
                case 'reminders':
                    $this->reminders();
                    break;
                case 'economy':
                    $this->economy();
                    break;
                case 'translation':
                    $this->translation();
                    break;
                case 'business':
                    $this->business();
                    break;
                default:
                    $this->main_menu();
            }
        } else {
            $this->main_menu();
        }

        if ($this->Message->isCallback())
            Telegram::edit_inline_message($this->Message->Chat->id, $this->Message->message_id, $this->out, $this->keyboard);
        else Telegram::talk_inline_keyboard($this->Message->Chat->id, $this->out, $this->keyboard);
        return true;
    }
}