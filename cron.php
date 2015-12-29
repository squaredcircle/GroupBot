<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 19/11/2015
 * Time: 1:43 AM
 */

spl_autoload_register( function ($class) {
    $include_path = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    return include(__DIR__ . '/' . $include_path);
});

require(__DIR__ . '/GroupBot/Settings.php');

if (isset($argv[1]))
{
    if ($argv[1] == 'resetDailyCounters')
    {
        $db = new \GroupBot\Base\DbControl();
        $db->resetDailyCounters();
    }
    elseif ($argv[1] == 'runRandomEvent')
    {
        $Coin = new \GroupBot\Brains\Coin\Coin();
        $Coin->runRandomEvent();
    }
}