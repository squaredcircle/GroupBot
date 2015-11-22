<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:30 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Coin;
use GroupBot\Types\Command;

class i_send extends Command
{
    public function i_send()
    {
        $ic = new Coin();

        if ($this->noParams() == 2) {
            if ($ic->performTransaction($this->Message->User->id, $this->getParam(), $this->getParam(1), $this->Telegram)) {
                if ($fb = $ic->getFeedback()) {
                    $this->Telegram->talk($this->Message->Chat->id, emoji("0x1F4E2") . " " . $fb);
                } else {
                    $this->Telegram->talk($this->Message->Chat->id, "You sent " . $this->getParam() . " " . $this->getParam(1) . ", gj brah");
                }
            } else {
                $this->Telegram->talk($this->Message->Chat->id, emoji("0x1F440") . " You gotta /link your account first brah...");
            }
        } else {
            $this->Telegram->talk($this->Message->Chat->id, "Like this fam " . emoji("0x1F449") . "  /send alex 10");
        }
    }
}