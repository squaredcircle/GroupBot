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

$db = new \GroupBot\Base\DbControl();
$db->resetDailyCounters();