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

require 'vendor/autoload.php';

require(__DIR__ . '/GroupBot/Libraries/common.php');
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

    switch ($argv[1]) {
        case 'resetDailyCounters':
            $db = new \GroupBot\Database\Cron($db);
            $db->resetDailyCounters();
            break;
        case 'runRandomEvent':
            $Coin = new \GroupBot\Brains\Coin\Money\Events($db);
            //$Coin->eventRoulette();
            break;
        case 'sendReminders':
            $Reminder = new GroupBot\Brains\Reminder\Control($db);
            $Reminder->sendReminders();
            break;
    }
}