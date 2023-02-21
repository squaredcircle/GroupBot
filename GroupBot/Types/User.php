<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 15/11/2015
 * Time: 6:46 PM
 */

namespace GroupBot\Types;


use GroupBot\Telegram;
use GroupBot\Libraries\Dictionary;

class User
{
    /** @var  integer */
    public int $user_id;
    
    /** @var  string */
    public string $first_name;
    
    /** @var  string|null */
    public ?string $user_name;
    
    /** @var  string|null */
    public ?string $last_name;

    /** @var  float */
    public float $balance;
    
    /** @var  integer */
    public int $level;
    
    public $last_activity;

    /** @var  boolean */
    public bool $received_income_today;

    /** @var  integer */
    public int $free_bets_today;

    /** @var  string */
    public string $handle_preference;

    /** @var  boolean */
    public bool $welcome_sent;

    /** @var  string|null */
    public ?string $timezone = null;

    /** @var  string|null */
    public ?string $location = null;

    public static function constructFromTelegramUpdate($user_update, \PDO $db)
    {
        if (isset($user_update['username']) &&  strcmp($user_update['username'], BOT_FULL_USER_NAME) === 0) {
            $user = new User();
            $user->user_name = BOT_FULL_USER_NAME;
            $user->welcome_sent = true;
            return $user;
        }

        $changed = false;

        $userSQL = new \GroupBot\Database\User($db);
        if ($user = $userSQL->getUserFromId($user_update['id'])) {
            if (isset($user_update['first_name']) &&strcmp($user->first_name, $user_update['first_name']) !== 0) {
                $user->first_name = $user_update['first_name'];
                $changed = true;
            }
            if (isset($user_update['username']) && strcmp($user->user_name, $user_update['username']) !== 0) {
                $user->user_name = $user_update['username'];
                $changed = true;
            }
            if (isset($user_update['last_name']) && strcmp($user->last_name, $user_update['last_name']) !== 0) {
                $user->last_name = $user_update['last_name'];
                $changed = true;
            }
        } else {
            $user = new User();
            $last_name = isset($user_update['last_name']) ? $user_update['last_name'] : NULL;
            $username = isset($user_update['username']) ? $user_update['username'] : NULL;
            $user->construct($user_update['id'], $user_update['first_name'], $last_name, $username);

            $changed = true;
        }
        if ($changed) $user->save($db);
        return $user;
    }

    public function construct($user_id, $first_name, $last_name = NULL, $user_name = NULL, $balance = 0, $level = 1, $last_activity = NULL, $received_income_today = 0, $free_bets_today = 0, $handle_preference = 'username', $welcome_sent = false, $timezone = NULL)
    {
        $this->user_id = $user_id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->user_name = $user_name;
        $this->balance = $balance;
        $this->level = $level;
        $this->last_activity = $last_activity;
        $this->received_income_today = $received_income_today;
        $this->free_bets_today = $free_bets_today;
        $this->handle_preference  = $handle_preference;
        $this->welcome_sent = $welcome_sent;
        $this->timezone = $timezone;
    }

    public function save(\PDO $db)
    {
        $userSQL = new \GroupBot\Database\User($db);
        return $userSQL->updateUser($this);
    }

    public function getLocation()
    {
        $loc = new Location();
        $loc->createFromSQL($this->location);
        return $loc;
    }

    public function getName()
    {
        if ($this->handle_preference == 'fullname') {
            if (isset($this->last_name)) return $this->first_name . " " . $this->last_name;
            return $this->first_name;
        }
        if (isset($this->user_name)) return $this->user_name;
        if (isset($this->last_name)) return $this->first_name . " " . $this->last_name;
        return $this->first_name;
    }

    public function getPrefixedUserName()
    {
        if (isset($this->user_name)) return "@" . $this->user_name;
        return $this->getName();
    }


    public function getBalance($high_precision = false)
    {
        return $high_precision ? $this->balance : round($this->balance, 2);
    }

    public function getTitle()
    {
        $dict = new Dictionary();

        if ($this->level > count($dict->level_titles)) {
            return end($dict->level_titles);
        } else {
            return $dict->level_titles[$this->level];
        }
    }

    public function getLevelAndTitle()
    {
        return "*Level " . $this->level . " " . $this->getTitle() . "*";
    }

    public function getNameLevelAndTitle()
    {
        return "*" . $this->getName() . "*, the *Level " . $this->level . " " . $this->getTitle() . "*";
    }
}