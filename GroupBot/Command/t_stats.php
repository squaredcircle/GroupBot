<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 19/11/2015
 * Time: 11:01 AM
 */
namespace GroupBot\Command;

use GroupBot\Base\Logging;
use GroupBot\Base\Telegram;
use GroupBot\Brains\Blackjack\Database\Control;
use GroupBot\Brains\Blackjack\SQL;
use GroupBot\Brains\Coin\Coin;
use GroupBot\Types\Command;

class t_stats extends Command
{
    private function plural_grammar($no)
    {
        if ($no == 1)
            return '';
        else
            return 's';
    }

    public function t_stats()
    {
        $log = new Logging($this->Message);
        $Coin = new Coin();
        $BlackjackSQL = new SQL();
        $CasinowarSQL = new \GroupBot\Brains\Casinowar\SQL();

        if ($this->isParam()) {
            $user_id = $log->checkIfUserIsLogged($this->getParam());
            if (!$user_id) {
                Telegram::talk($this->Message->Chat->id, "can't find that user, brah");
                return false;
            }
        } else {
            $user_id = $this->Message->User->id;
        }

        $log = $log->getAllUserLogsForChat($user_id);
        $bj_stats = $BlackjackSQL->select_player_stats($user_id);
        $cw_stats = $CasinowarSQL->select_player_stats($user_id);
        $CoinUser = $Coin->SQL->GetUserById($user_id);

        $date = 0;
        foreach ($log->LogsCommand as $cmd) {
            if (strtotime($cmd->last_used) > $date) {
                $last_cmd = $cmd;
                $date = strtotime($cmd->last_used);
            }
        }
        if (!isset($last_cmd)) return false;

        $out = emoji(0x1F4C8) . "*" . $this->Message->Chat->title . "* stats for *" .$log->User->first_name . " " . $log->User->last_name . "*."
            . "\n`   `•` " . $log->posts_today . "` message" . $this->plural_grammar($log->posts_today) . " sent today"
            . "\n`   `•` " . $log->posts       . "` message" . $this->plural_grammar($log->posts)       . " sent ever"
            . "\n`   `•` " . round(86400 * $log->posts / (strtotime("now") - strtotime("2015-11-19 11:00:00")), 0) . "` messages sent per day, on average"
            . "\nLast message `(" . date('D jS g:iA', strtotime($log->lastpost_date)) . ")`:"
            . "\n`   `_" . $log->lastpost . "_"
            . "\nLast command: `" . $last_cmd->command . "`"
            . "\n`   `•` " . $last_cmd->uses_today . "` use" . $this->plural_grammar($last_cmd->uses_today) . " today"
            . "\n`   `•` " . $last_cmd->uses       . "` use" . $this->plural_grammar($last_cmd->uses_today) . " ever";

        $last_coin_activity = isset($CoinUser->last_activity) ? date('D jS g:iA', strtotime($CoinUser->last_activity)) : "not recorded";

        $out .= "\n\n"
            . emoji(0x1F4B2) . "*" . COIN_CURRENCY_NAME . "* stats:"
            . "\n`   `•` " . $CoinUser->getBalance() . "`" .  emoji(0x1F4B0) . " in the bank"
            . "\n`   `•` " . $Coin->SQL->GetNumberOfTransactionsByUser($CoinUser) . "` outgoing transactions ever"
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