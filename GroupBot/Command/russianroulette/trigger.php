<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:07 AM
 */
namespace GroupBot\Command\russianroulette;

use GroupBot\Brains\RussianRoulette\RussianRoulette;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class trigger extends Command
{
    private function getFace()
    {
        $faces = [
            emoji(0x1F60E), // SMILING FACE WITH SUNGLASSES
            emoji(0x1F609), // WINKING FACE
            emoji(0x1F610), // NEUTRAL FACE
            emoji(0x1F623), // PERSEVERING FACE
            emoji(0x1F611), // EXPRESSIONLESS FACE
            emoji(0x1F636), // FACE WITHOUT MOUTH
            emoji(0x1F629), // WEARY FACE
            emoji(0x1F62C), // GRIMACING FACE
            emoji(0x1F633), // FLUSHED FACE
            emoji(0x1F621), // POUTING FACE
            emoji(0x1F620), // ANGRY FACE
            emoji(0x1F613), // FACE WITH COLD SWEAT
            emoji(0x1F612), // UNAMUSED FACE
            emoji(0x1F628), // FEARFUL FACE
            emoji(0x1F616), // CONFOUNDED FACE
            emoji(0x1F624), // FACE WITH LOOK OF TRIUMPH
            emoji(0x1F62D), // LOUDLY CRYING FACE
            emoji(0x1F622), // CRYING FACE
            emoji(0x1F630) // FACE WITH OPEN MOUTH AND COLD SWEAT
        ];

        return $faces[mt_rand(0, count($faces) - 1)];
    }

    public function main()
    {
        $RussianRoulette = new RussianRoulette($this->db, $this->Message->Chat->id, $this->Message->User->user_id);

        if ($RussianRoulette->isLoaded()) {
            if ($RussianRoulette->trigger()) {
                $out = emoji(0x1F4A5) . emoji(0x1F52B)
                    . "\n"
                    . "\n" . emoji(0x2620) . " *" . $this->Message->User->getName() . "* killed themselves."
                    . "\n /reload to play again.";
                Telegram::talkForced($this->Message->Chat->id, $out);
                Telegram::kick($this->Message->Chat->id, $this->Message->User->user_id);
            } else {
                $out = $this->getFace() . emoji(0x1F52B) . " `Click.`";
            }
        } else {
            $out = emoji(0x1F449) . " The revolver isn't loaded. Use /reload first.";
        }

        Telegram::talk($this->Message->Chat->id, $out);
    }
}