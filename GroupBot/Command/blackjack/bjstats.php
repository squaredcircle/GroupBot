<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 8/11/2015
 * Time: 12:14 AM
 */
namespace GroupBot\Command\blackjack;

use GroupBot\Brains\Blackjack\SQL;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class bjstats extends Command
{
    public function main()
    {
        $BlackjackSQL = new SQL($this->db);

        $bj_stats = $BlackjackSQL->select_global_stats();

        $out = '';

        if ($bj_stats) {
            $out .=
                  "🃏 Global *blackjack* stats:"
                . "\n`   `•` {$bj_stats->games_played}` games ever with `{$bj_stats->wins}:{$bj_stats->losses}:{$bj_stats->draws}` _(W:L:D)_"
                . "\n`   `•` {$bj_stats->hits}` hits, `{$bj_stats->stands}` stands, `{$bj_stats->surrenders}` surrenders"
                . "\n`   `•` {$bj_stats->splits}` splits, `{$bj_stats->doubledowns}` doubledowns, `{$bj_stats->blackjacks}` blackjacks"
                . "\n`   `•` {round($bj_stats->total_coin_bet,2)}` 💰 bet ever";
        }

        Telegram::talk($this->Message->Chat->id, $out);
        return true;
    }
}