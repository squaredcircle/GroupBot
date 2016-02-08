<?php

namespace GroupBot\Brains\Coin;

use GroupBot\Brains\Coin\Logging\Leaderboard;
use GroupBot\Brains\Coin\Logging\RecentLogs;
use GroupBot\Brains\Coin\Money\Events;
use GroupBot\Brains\Coin\Money\Transact;
use GroupBot\Brains\Coin\Types\CoinUser;
use GroupBot\Types\User;

class Coin
{
    public $SQL, $Feedback;
    public $Leaderboard, $RecentLogs, $Transact;

	public function __construct()
	{
        $this->Feedback = new Feedback();
        $this->SQL = new SQL($this->Feedback);
        $this->Transact = new Transact($this->SQL, $this->Feedback);
	}

    public function getLeaderboard()
    {
        return new Leaderboard($this->SQL);
    }

    public function getRecentLogs()
    {
        return new RecentLogs($this->SQL);
    }

    public function runRandomEvent()
    {
        $Events = new Events($this->SQL, $this->Feedback);
        $Events->eventRoulette();
    }

    public function checkForAndCreateUser(User $User)
    {
        if (!$this->SQL->DoesUserExistById($User->id)) {
            $username = $User->hasUserName() ? $User->user_name : $User->first_name;

            $username_new = $username; $index = 2;
            while ($this->SQL->DoesUserExistByName($username_new)) {
                $username_new = $username . $index;
            }
            
            $CoinUser = new CoinUser(
                $User->id,
                $username_new,
                0
            );
            $this->SQL->CreateNewUser($CoinUser);
            return true;
        }
        return false;
    }
}