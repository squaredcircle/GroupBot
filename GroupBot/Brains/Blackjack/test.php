<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 13/12/2015
 * Time: 11:31 AM
 */

spl_autoload_register( function ($class) {
    $include_path = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists('/var/www/ssl/telegram/Shit2Bot/' . $include_path))
        return include('/var/www/ssl/telegram/Shit2Bot/' . $include_path);
    else
        return false;
});


use GroupBot\Brains\Blackjack\Enums\PlayerMove;
include(__DIR__ . '/../../Settings.php');


$bj = new \GroupBot\Brains\Blackjack\Blackjack('28', '50', new PlayerMove(PlayerMove::Stand));

