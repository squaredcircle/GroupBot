<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:11 AM
 */
namespace GroupBot\Command;

use GroupBot\Base\Telegram;
use GroupBot\Types\Command;

class t_help extends Command
{

    /*
echo - It's like talking to a wall!
check - Except this wall talks back?
link - Shit does this one even work?
spookyecho - Wow guys I dno about this
send - help please
leaderboard - of the incomplete
recent - ly i thought
stats - about things
emoji - can't save this GroupBot
person - who made this one jesus
spookyperson - oh please no
zalgo - you wouldn't
zalgomin - that's nice
zalgomax - rusrs
spookyzalgoperson - pls stop
photo - of what?
isaaccoin - sucks lol
 */

    public function t_help()
    {
        $response =
            "GroupBot - Your premier shitposting solution " . emoji("0x1F444") . "

/echo - It's like talking to a wall!
/check - Except this wall talks back?
/link - Shit does this one even work?
/spookyecho - Wow guys I dno about this
/send help please
/leaderboard of the incomplete
/recent ly i thought
/stats about things
/emoji can't save this " . BOT_FRIENDLY_NAME . "
/person who made this one jesus
/spookyperson oh please no
/zalgo you wouldn't
/zalgomin that's nice
/zalgomax rusrs
/spookyzalgoperson pls stop
/photo of what?
/isaaccoin sucks lol";
        Telegram::talk($this->Message->Chat->id, $response);
    }
}