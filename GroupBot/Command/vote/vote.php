<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:30 AM
 */
namespace GroupBot\Command\vote;

use GroupBot\Brains\Query;
use GroupBot\Telegram;
use GroupBot\Brains\Vote\Enums\VoteType;
use GroupBot\Brains\Vote\Types\UserVote;
use GroupBot\Types\Chat;
use GroupBot\Types\Command;
use GroupBot\Types\User;

class vote extends Command
{
    /** @var  Chat */
    private $chat;
    private $out;
    private $keyboard;
    private $vote_cast = false;

    private function leaderboard()
    {
        $Vote = new \GroupBot\Brains\Vote\Vote($this->db);
        $leaderboard = $Vote->getVoteLeaderboard($this->chat->id);

        $out = '';
        $index = 0;

        if (!empty($leaderboard)) {
            foreach ($leaderboard as $uservote) {
                $index++;
                $out .= "`" . addOrdinalNumberSuffix($index);
                if ($index >= 10) {
                    $out .= " `";
                } else {
                    $out .= "  `";
                }

                $vote_prefix = $uservote->vote_total > 0 ? "+" : "";
                if (!isset($uservote->vote_total))
                    $uservote->vote_total = 0;

                $out .= "*" . $uservote->user->getName() . "* (" . $vote_prefix . $uservote->vote_total . ")\n";
            }
        } else {
            $out .= "No users to display.";
        }

        return $out;
    }

    private function performVote()
    {
        $user = Query::getUserMatchingStringOrErrorMessage($this->db, $this->chat, $this->getParam(0));
        if (is_string($user))
            return $user;

        if (strcasecmp($this->getParam(0), BOT_FRIENDLY_NAME) === 0) {
            return "wow, thx brah! " . emoji(0x1F618);
        }
        if (!$user) {
            return emoji(0x1F44E) . " Can't find that user, brah";
        }
        if ($user->user_id == $this->Message->User->user_id) {
            return emoji(0x1F44E) . " You can't vote for yourself!";
        }
        switch ($this->getParam(1)) {
            case 'up':
                $voteType = new VoteType(VoteType::Up);
                break;
            case 'down':
                $voteType = new VoteType(VoteType::Down);
                break;
            case 'neutral':
                $voteType = new VoteType(VoteType::Neutral);
                break;
            default:
                return emoji(0x1F44E) . " Your vote must be either *up*, *down* or *neutral*.";
        }

        $voted_for = new User();
        $voted_for->user_id = $user->user_id;
        $userVote = new UserVote();
        $userVote->construct($this->Message->User, $voted_for, $voteType);

        $Vote = new \GroupBot\Brains\Vote\Vote($this->db);
        $Vote->SQL->update_vote($userVote);

        $this->vote_cast = true;

        return emoji(0x1F528) . " Vote updated.";
    }

    private function displayLeaderboard()
    {
        if (isset($this->chat)) {
            if ($this->vote_cast) {
                $out = "\nThe leaderboard for *" . $this->chat->title . "* is now:\n\n";
            } else {
                $out = "Voting leaderboard for *" . $this->chat->title . "*:\n\n";
            }
        } else {
            if ($this->vote_cast) {
                $out = "\nThe *global* voting leaderboard is now:\n\n";
            } else {
                $out = "*Global* voting leaderboard:\n\n";
            }
        }

        $out .= $this->leaderboard();

        return $out;
    }

    private function displayInstructions()
    {
        if (!$this->Message->Chat->isPrivate()) {
            if ($this->vote_cast) {
                $out = "\nYou can see your votes with /myvotes.";
            } else {
                $out = "\nYou can vote for others like this " . emoji("0x1F449") . "  `/vote richardstallman up`\nYou can see your votes with /myvotes.";
            }
        } else {
            $out = "\nYou can vote for others like this " . emoji("0x1F449") . "  `/vote richardstallman up`"
                    ."\nYou can view the leaderboards for these recent chats:";
            $this->keyboard = $this->keyboard();
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
                        'callback_data' => '/vote'
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
                'callback_data' => '/vote _view_chat ' . $chat->id
            ];
        }
        return $keyboard;
    }

    public function main()
    {
        if ($this->Message->Chat->isPrivate()) {
            $this->chat = NULL;
        } else {
            $this->chat = $this->Message->Chat;
        }

        if ($this->noParams() == 2) {
            if (strcmp($this->getParam(), '_view_chat') === 0) {
                $DbChat = new \GroupBot\Database\Chat($this->db);
                if ($this->chat = $DbChat->getChatById($this->getParam(1))) {
                    $out = $this->displayLeaderboard();
                    $out .= $this->displayInstructions();
                } else {
                    $out = emoji(0x1F44E) . " Can't find that chat, displaying the Global Leaderboard instead.\n\n";
                    $this->chat = NULL;
                    $out .= $this->displayLeaderboard();
                    $out .= $this->displayInstructions();
                }
            } else {
                $out = $this->performVote();
                $out .= $this->displayLeaderboard();
                $out .= $this->displayInstructions();
            }
        } else {
            $out = $this->displayLeaderboard();
            $out .= $this->displayInstructions();
        }

        if ($this->Message->Chat->isPrivate()) {
            if ($this->Message->isCallback())
                Telegram::edit_inline_message($this->Message->Chat->id, $this->Message->message_id, $out, $this->keyboard);
            else Telegram::talk_inline_keyboard($this->Message->Chat->id, $out, $this->keyboard);
        } else {
            Telegram::talk($this->Message->Chat->id, $out);
        }
        return true;
    }
}