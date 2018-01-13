<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:30 AM
 */
namespace GroupBot\Command\todo;

use GroupBot\Brains\Query;
use GroupBot\Brains\Todo\Types\TodoItem;
use GroupBot\Command\misc\emoji;
use GroupBot\Telegram;
use GroupBot\Brains\Todo\Enums\VoteType;
use GroupBot\Brains\Todo\Types\UserVote;
use GroupBot\Types\Command;
use GroupBot\Types\User;

class todo extends Command
{
    private $out;
    private $keyboard;
    private $vote_cast = false;

    /** @var  \GroupBot\Brains\Todo\Todo */
    private $Todo;

    private function leaderboard()
    {
        $leaderboard = $this->Todo->getVoteLeaderboard();

        $out = '';
        $index = 0;

        if (!empty($leaderboard)) {
            foreach ($leaderboard as $uservote) {

                $vote_prefix = $uservote->vote_total > 0 ? "+" : "";
                if (!isset($uservote->vote_total))
                    $uservote->vote_total = 0;

                $index++;
                $out .= "`" . addOrdinalNumberSuffix($index);

                if ($uservote->vote_total != 0) {
                    if ($index >= 10) {
                        $out .= " `";
                    } else {
                        $out .= "  `";
                    }
                    if ($uservote->vote_total >= 10) {
                        $out .= "_$vote_prefix" . $uservote->vote_total . "  _ * ";
                    } elseif ($uservote->vote_total <= -10) {
                        $out .= " _$vote_prefix" . $uservote->vote_total . "  _ * ";
                    } elseif ($uservote->vote_total < 0) {
                        $out .= "_ $vote_prefix" . $uservote->vote_total . "     _ * ";
                    } else {
                        $out .= "_ $vote_prefix" . $uservote->vote_total . "    _ * ";
                    }
                } else {
                    if ($index >= 10) {
                        $out .= " `_" . $uservote->vote_total . "   _ * ";
                    } else {
                        $out .= "   `_" . $uservote->vote_total . "     _ * ";
                    }
                }

                $out .= $uservote->item->description . "*\n";
            }
        } else {
            $out .= "No todo items to display.";
        }

        return $out;
    }

    private function addTodo($description, User $owner)
    {
        $todoItem = new TodoItem($description, $owner);
        $todoItem->id = $this->Todo->SQL->add_todo($todoItem);
        $out = $this->display_single($todoItem);
        return $out;
    }

    private function performVote($todo_id, $vote, $voter)
    {
        switch ($vote) {
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

        $todoItem = new TodoItem(null, null);
        $todoItem->id = $todo_id;
        $userVote = new UserVote($voter, $todoItem, $voteType);

        if ($this->Todo->SQL->check_if_vote_exists($userVote)) {
            $this->vote_cast = true;
            return emoji(0x1F528) . " Vote unchanged - you've voted this way before!";
        }
        $this->Todo->SQL->update_vote($userVote);
        $rank = $this->Todo->getVoteTotalForTodo($todo_id);
        $this->vote_cast = true;
        $vote_prefix = $rank > 0 ? "+" : "";
        return emoji(0x1F528) . " Vote updated. *" . $userVote->todo->id . "* is now on *$vote_prefix$rank*";
    }

    private function displayLeaderboard()
    {

        if ($this->vote_cast) {
            $out = "\nThe todo leaderboard is now:\n\n";
        } else {
            $out = "Todo leaderboard:\n\n";
        }

        $out .= $this->leaderboard();

        return $out;
    }

    private function displayInstructions()
    {
        $out = "\nAdd a new item with `/todo Add a new feature`";
        return $out;
    }

    private function display_single(TodoItem $todo)
    {
        $out = emoji(0x1F4C3) . " *ShitBot Todo List*"
            . "\nItem *$todo->id*, from *" . $todo->owner->getName() . "*:"
            . "\n```$todo->description```";
        $this->keyboard = $this->keyboard_single($todo);
        return $out;
    }

    private function keyboard_single(TodoItem $todo)
    {
        $votes = $this->Todo->getVoteTalliesForTodo($todo->id);
        $keyboard =
        [
            [
                [
                    'text' => "$votes->up 👍",
                    'callback_data' => "/todo $todo->id up"
                ],
                [
                    'text' => "$votes->neutral ⎯",
                    'callback_data' => "/todo $todo->id neutral"
                ],
                [
                    'text' => emoji(0x1F6AA) . "$votes->up 👎",
                    'callback_data' => "/todo $todo->id down"
                ]
            ]
        ];
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

    private function checkVote($id, $vote)
    {
        return (
            is_int($id) && (
                strcmp($vote, 'up') === 0 ||
                strcmp($vote, 'down') === 0 ||
                strcmp($vote, 'neutral') === 0
            )
        );
    }

    public function main()
    {
        $this->Todo = new \GroupBot\Brains\Todo\Todo($this->db);

        if ($this->noParams() == 2 &&
            $this->Todo->SQL->check_if_todo_exists($this->getParam()) &&
            $this->checkVote($this->getParam(0), $this->getParam(1))
        ) {
            $out = $this->performVote($this->getParam(), $this->getParam(1), $this->Message->User);
        } elseif ($this->noParams() > 3) {
            $out = $this->addTodo($this->getAllParams(), $this->Message->User);
        } else {
            $out = $this->displayLeaderboard();
            $out .= $this->displayInstructions();
        }

        if ($this->Message->Chat->isPrivate()) {
            if ($this->Message->isCallback())
                Telegram::edit_inline_message($this->Message->Chat->id, $this->Message->message_id, $out, $this->keyboard);
            else Telegram::talk($this->Message->Chat->id, $out);
        } else {
            Telegram::talk($this->Message->Chat->id, $out);
        }
        return true;
    }
}