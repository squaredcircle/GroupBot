<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 19/11/2015
 * Time: 11:01 AM
 */
namespace GroupBot\Command;

use GroupBot\Brains\Query;
use GroupBot\Database\User;
use GroupBot\Telegram;
use GroupBot\Brains\Blackjack\SQL;
use GroupBot\Types\Command;

class stats extends Command
{
    private function plural_grammar($no)
    {
        if ($no == 1)
            return '';
        else
            return 's';
    }

    public function main()
    {

        if ($this->isParam()) {
            $user = Query::getUserMatchingStringOrErrorMessage($this->db, $this->Message->Chat, $this->getParam());

            if (is_string($user)) {
                Telegram::talk($this->Message->Chat->id, $user);
                return false;
            }
        } else {
            $user = $this->Message->User;
        }

        $DbUser = new User($this->db);
        $BlackjackSQL = new SQL($this->db);
        $CoinSQL = new \GroupBot\Brains\Coin\SQL($this->db);

        //Telegram::talk_no_markdown($this->Message->Chat->id, print_r($user, true));
        //return true;

        $stats = $DbUser->getUserPostStatsInChat($this->Message->Chat, $user);
       // $cmdstats = $DbUser->getUserCommandStatsInChat($this->Message->Chat, $user);
        $bj_stats = $BlackjackSQL->select_player_stats($user->user_id);
        $cw_stats = false;

//        $date = 0;
//        foreach ($log->LogsCommand as $cmd) {
//            if (strtotime($cmd->last_used) > $date) {
//                $last_cmd = $cmd;
//                $date = strtotime($cmd->last_used);
//            }
//        }
//        if (!isset($last_cmd)) return false;

        $out = emoji(0x1F4C8) . "*" . $this->Message->Chat->title . "* stats for "
            . "\n" . $user->getNameLevelAndTitle()
            . "\n`   `•` " . $stats->posts_today . "` message" . $this->plural_grammar($stats->posts_today) . " sent today"
            . "\n`   `•` " . $stats->posts       . "` message" . $this->plural_grammar($stats->posts)       . " sent ever"
            . "\n`   `•` " . round(86400 * $stats->posts / (strtotime("now") - strtotime("2015-11-19 11:00:00")), 0) . "` messages sent per day, on average"
            . "\nLast message `(" . date('D jS g:iA', strtotime($stats->lastpost_date)) . ")`:"
            . "\n`   `_" . $stats->lastpost . "_";
//            . "\nLast command: `" . $last_cmd->command . "`"
//            . "\n`   `•` " . $last_cmd->uses_today . "` use" . $this->plural_grammar($last_cmd->uses_today) . " today"
//            . "\n`   `•` " . $last_cmd->uses       . "` use" . $this->plural_grammar($last_cmd->uses_today) . " ever";

        $last_coin_activity = isset($user->last_activity) ? date('D jS g:iA', strtotime($user->last_activity)) : "not recorded";

        $out .= "\n\n"
            . emoji(0x1F4B2) . "*" . COIN_CURRENCY_NAME . "* stats:"
            . "\n`   `•` " . $user->getBalance() . "`" .  emoji(0x1F4B0) . " in the bank"
            . "\n`   `•` " . $CoinSQL->getNumberOfTransactionsByUser($user) . "` outgoing transactions ever"
            . "\n`   `•`  `Last activity: `" . $last_coin_activity . "`";

        if ($bj_stats) {
            $bj_balance = $bj_stats->coin_won - $bj_stats->coin_lost;
            $out .= "\n\n"
                . emoji(0x1F0CF) . "*Blackjack* stats:"
                . "\n`   `•` " . $bj_stats->games_played . "` games ever with `" . $bj_stats->wins . ":" . $bj_stats->losses . ":" . $bj_stats->draws . "` _(W:L:D)_"
                . "\n`   `•` " . $bj_stats->hits . "` hits, `" . $bj_stats->stands . "` stands, `" . $bj_stats->surrenders . "` surrenders"
                . "\n`   `•` " . $bj_stats->splits . "` splits, `" . $bj_stats->doubledowns . "` doubledowns, `" . $bj_stats->blackjacks . "` blackjacks"
                . "\n`   `•` " . round($bj_stats->total_coin_bet,2) . "`" . emoji(0x1F4B0) . " bet ever, currently " .
                ($bj_balance == 0 ? "breaking even at `" : ($bj_balance > 0 ? "up `" : "down `")) . round($bj_balance, 2) . "`" . emoji(0x1F4B0);
        }

        if ($cw_stats) {
            $cw_balance = $cw_stats->coin_won - $cw_stats->coin_lost;
            $out .= "\n\n"
                . emoji(0x1F0CF) . "*Casino war* stats:"
                . "\n`   `•` " . $cw_stats->games_played . "` games ever with `" . $cw_stats->wins . ":" . $cw_stats->losses. "` _(W:L)_"
                . "\n`   `•` " . $cw_stats->wars . "` wars and `" . $cw_stats->surrenders . "` surrenders."
                . "\n`   `•` " . round($cw_stats->total_coin_bet,2) . "`" . emoji(0x1F4B0) . " bet ever, currently " .
                ($cw_balance == 0 ? "breaking even at `" : ($cw_balance > 0 ? "up `" : "down `")) . round($cw_balance, 2) . "`" . emoji(0x1F4B0);
        }

        Telegram::talk($this->Message->Chat->id, $out);
        return true;
    }
}