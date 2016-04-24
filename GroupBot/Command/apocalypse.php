<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:30 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Coin\Enums\TransactionType;
use GroupBot\Brains\Coin\Money\Transact;
use GroupBot\Brains\Coin\Types\BankTransaction;
use GroupBot\Brains\Vote\Enums\VoteType;
use GroupBot\Brains\Vote\Types\UserVote;
use GroupBot\Brains\Vote\Vote;
use GroupBot\Database\User;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class apocalypse extends Command
{
    /** @var  string */
    private $out;

    private function loseAllMoney()
    {
        $this->out .= "\n" . emoji(0x1F4A5) . " The heavens part, and the hand of god approaches you... ";

        $Transact = new Transact($this->db);
        $Transact->transactToBank(new BankTransaction(
            $this->Message->User,
            $this->Message->User->getBalance(true),
            new TransactionType(TransactionType::LevelPurchase)
        ));

        $this->out .= "\n*" . $this->Message->User->getName() . "*, it takes all of your " . COIN_CURRENCY_NAME . ".";
    }

    private function dropOneLevel()
    {
        $this->out .= "\n" . emoji(0x1F407) . " A nice bunny rabbit approaches from distance. ";

        $this->Message->User->level--;
        $this->Message->User->save($this->db);

        $this->out .= "\n" . " It bites you. *" . $this->Message->User->getName() . "*, you lose a level. ";
    }

    private function loseFreeBets()
    {
        $this->out .= "\n" . emoji(0x1F480) . " The ground gives way, and you hear the screams of the damned from below. ";

        $this->Message->User->free_bets_today = 100;
        $this->Message->User->save($this->db);

        $this->out .= "\n*" . $this->Message->User->getName() . "*, your remaining free bets today are sucked away. ";
    }

    private function losePopularity()
    {
        $this->out .= "\n" . emoji(0x1F465) . " Sickening groans surround you, as corpses rise from the earth. ";

        $Vote = new Vote($this->db);
        $DbUser = new User($this->db);
        $users_in_chat = $DbUser->getAllUsersInChat($this->Message->Chat->id);

        foreach ($users_in_chat as $user) {
            if ($user->user_id != $this->Message->User->user_id) {
                $userVote = new UserVote();
                $userVote->construct($user, $this->Message->User, new VoteType(VoteType::Down));
                $Vote->SQL->update_vote($userVote);
            }
        }

        $this->out .= "\n*" . $this->Message->User->getName() . "*, you become very unpopular. ";
    }

    public function main()
    {
        $this->out = emoji(0x1F4A2) . " *A great rumbling fills the earth...* " . emoji(0x1F4A2) . "\n";

        if ($this->Message->User->getBalance() > 0   && $this->Message->User->level >  1) {
            $choices = ['loseFreeBets', 'losePopularity', 'loseAllMoney', 'dropOneLevel'];
        } elseif ($this->Message->User->getBalance() > 0   && $this->Message->User->level == 1) {
            $choices = ['loseFreeBets', 'losePopularity', 'loseAllMoney'];
        } elseif ($this->Message->User->getBalance() == 0  && $this->Message->User->level >  1) {
            $choices = ['loseFreeBets', 'losePopularity', 'dropOneLevel'];
        } else {
            $choices = ['loseFreeBets', 'losePopularity'];
        }

       $this->{$choices[mt_rand(0, count($choices)-1)]}();

        Telegram::talk($this->Message->Chat->id, $this->out);
    }
}