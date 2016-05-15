<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 14/05/2016
 * Time: 7:02 PM
 */

namespace GroupBot\Brains;


class Speak
{
    private static function tts_hts($timestamp)
    {
        $dir = "/var/www/html/bot";
        exec("$dir/hts/flite+hts_engine-1.06/bin/flite_hts_engine -m $dir/hts/hts_voice_cmu_us_arctic_slt-1.05/cmu_us_arctic_slt.htsvoice -r 0.9 -g '9' -o $dir/speech/$timestamp.wav $dir/speech/$timestamp.txt");
    }

    private static function tts_espeak($timestamp)
    {
        $dir = "/var/www/html/bot";
        exec("/usr/bin/espeak -f $dir/speech/$timestamp.txt -w $dir/speech/$timestamp.wav");
    }

    public static function createAudioFile($text, $engine = 'tts')
    {
        $dir = "/var/www/html/bot";
        $timestamp = round(microtime(true) * 1000);

        file_put_contents("$dir/speech/$timestamp.txt", $text);

        if (strcmp($engine, 'tts') === 0) self::tts_hts($timestamp);
        if (strcmp($engine, 'espeak') === 0) self::tts_espeak($timestamp);

        exec("/usr/local/bin/opusenc --quiet $dir/speech/$timestamp.wav $dir/speech/$timestamp.ogg");
        exec("rm $dir/speech/$timestamp.wav");

        return "$timestamp.ogg";
    }
}