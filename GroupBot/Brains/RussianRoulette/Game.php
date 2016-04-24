<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 24/04/2016
 * Time: 12:29 PM
 */

namespace GroupBot\Brains\RussianRoulette;


class Game
{
    public $chat_id;

    /** @var  integer */
    public $bullet_chamber;

    /** @var  integer */
    public $current_chamber;

    public function construct($chat_id, $bullet_chamber, $current_chamber)
    {
        $this->chat_id = $chat_id;
        $this->bullet_chamber = $bullet_chamber;
        $this->current_chamber = $current_chamber;
    }
}