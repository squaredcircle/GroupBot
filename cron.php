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

function createPDO()
{
    $pdo = new \PDO('mysql:host=' . BOT_DB_HOST . ';dbname=' . BOT_DB_NAME . ';charset=utf8', BOT_DB_USER, BOT_DB_PASSWORD);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
    return $pdo;
}

if (isset($argv[1]))
{
    $db = createPDO();

    if ($argv[1] == 'resetDailyCounters')
    {
        $db = new \GroupBot\Database\Cron($db);
        $db->resetDailyCounters();
    }
    elseif ($argv[1] == 'runRandomEvent')
    {
        $Coin = new \GroupBot\Brains\Coin\Coin();
        $Coin->runRandomEvent();
    }
}