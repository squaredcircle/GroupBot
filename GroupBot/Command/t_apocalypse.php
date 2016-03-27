<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:30 AM
 */
namespace GroupBot\Command;

use GroupBot\Base\DbControl;
use GroupBot\Telegram;
use GroupBot\Brains\Coin\Coin;
use GroupBot\Brains\Coin\Enums\TransactionType;
use GroupBot\Brains\Coin\Types\CoinUser;
use GroupBot\Brains\Coin\Types\Transaction;
use GroupBot\Brains\Level\Level;
use GroupBot\Brains\Vote\Enums\VoteType;
use GroupBot\Brains\Vote\Types\UserVote;
use GroupBot\Brains\Vote\Vote;
use GroupBot\Types\Command;

class t_apocalypse extends Command
{
    /** @var  Coin */
    private $Coin;

    /** @var  CoinUser */
    private $this_user;

    /** @var  string */
    private $out;

    /** @var  Level */
    private $Level;
    private $level;

    /** @var  DbControl */
    private $DbControl;

    private function loseAllMoney()
    {
        $this->out .= "\n" . emoji(0x1F4A5) . " The heavens part, and the hand of god approaches you... ";

        $user_receiving = $this->Coin->SQL->GetUserByName(COIN_TAXATION_BODY);
        $Transaction = new Transaction(
            $this->this_user,
            $user_receiving,
            $this->this_user->getBalance(),
            new TransactionType(TransactionType::Apocalypse)
        );
        $this->Coin->Transact->performTransaction($Transaction);

        $this->out .= "\n" . " It takes all of your " . COIN_CURRENCY_NAME . ".";
    }

    private function dropOneLevel()
    {
        $this->out .= "\n" . emoji(0x1F407) . " A nice bunny rabbit approaches from distance. ";
        $this->Level->SQL->update_level($this->Message->User->id, $this->level - 1);
        $this->out .= "\n" . " It bites you. You lose a level out of shame. ";
    }

    private function loseFreeBets()
    {
        $this->out .= "\n" . emoji(0x1F480) . " The ground gives way, and you hear the screams of the damned from below. ";

        $sql = 'INSERT INTO casino (user_id, free_bets_today)
                VALUES (:user_id, :free_bets_today)
                ON DUPLICATE KEY UPDATE
                  free_bets_today = 100';

        $db = $this->DbControl->getObject();

        $query = $db->prepare($sql);
        $query->bindValue(':user_id', $this->Message->User->id);
        $query->bindValue(':free_bets_today', 100);

        $query->execute();

        $this->out .= "\n" . " Your remaining free bets today are sucked away. ";
    }

    private function losePopularity()
    {
        $this->out .= "\n" . emoji(0x1F465) . " Sickening groans surround you, as corpses rise from the earth. ";

        $Vote = new Vote();
        $users_in_chat = $this->DbControl->getAllUsersInChat($this->Message->Chat->id);

        foreach ($users_in_chat as $user) {
            if ($user->id != $this->Message->User->id) {
                $uservote = new UserVote();
                $uservote->construct($user, $this->Message->User, new VoteType(VoteType::Down));
                $Vote->SQL->update_vote($uservote);
            }
        }

        $this->out .= "\n" . " You become very unpopular. ";
    }

    public function t_apocalypse()
    {
        $this->DbControl = new DbControl();
        $this->Level = new Level();
        $this->Coin = new Coin();
        $this->this_user = $this->Coin->SQL->GetUserById($this->Message->User->id);
        $this->level = $this->Level->SQL->get_level($this->Message->User->id);

        $this->out = emoji(0x1F4A2) . " *A great rumbling fills the area...* " . emoji(0x1F4A2);

        if       ($this->this_user->getBalance() > 0   && $this->level >  1) {
            $choices = ['loseFreeBets', 'losePopularity', 'loseAllMoney', 'dropOneLevel'];
        } elseif ($this->this_user->getBalance() > 0   && $this->level == 1) {
            $choices = ['loseFreeBets', 'losePopularity', 'loseAllMoney'];
        } elseif ($this->this_user->getBalance() == 0  && $this->level >  1) {
            $choices = ['loseFreeBets', 'losePopularity', 'dropOneLevel'];
        } else {
            $choices = ['loseFreeBets', 'losePopularity'];
        }

       $this->{$choices[mt_rand(0, count($choices)-1)]}();

        Telegram::talk($this->Message->Chat->id, $this->out);
    }
}