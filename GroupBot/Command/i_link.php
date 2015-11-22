<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:29 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Coin;
use GroupBot\Enums\ChatType;
use GroupBot\Types\Command;

include(__DIR__ . '/../libraries/random_str.php');

class i_link extends Command
{
    public function i_link()
    {
        $ic = new Coin();

        if ($this->Message->Chat->type == ChatType::Group) {
            $this->Telegram->talk($this->Message->Chat->id, "please fam, in private k?");
        } elseif ($ic->checkIfUserLinked($this->Message->User->id)) {
            $this->Telegram->talk($this->Message->Chat->id, "Your *Telegram* and *Isaac Coin* accounts are already linked, fam.\nIf you'd like to unlink, do so at the website:\nhttp://v5.crazyserver.net.au/coin");
        } else {
            $key = random_str(15, '0123456789bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ');


            $ic->createPendingLink($this->Message->User->id, $key);

            $url = "http://v5.crazyserver.net.au/coin/?action=register&id=";
            $text = emoji("0x1F44C") . " Ok brah. Click this URL to create an Isaac Coin account:\n "
                . $url . $this->Message->User->id . "&key=" . $key. " \n\nNotes: \n•This account be linked to your current Telegram account. \n•The URL will work until you request another or use it successfully.";

            $this->Telegram->talk($this->Message->Chat->id, $text);
        }
    }
}