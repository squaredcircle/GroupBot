<?php

spl_autoload_register( function ($class) {
	$include_path = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
	if (file_exists(__DIR__ . '/' . $include_path))
		return include(__DIR__ . '/' . $include_path);
	else
		return false;
});
spl_autoload_register( function ($class) {
	$include_path = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
	if (file_exists(COIN_PATH . '/' . $include_path))
		return include(COIN_PATH . '/' . $include_path);
	else
		return false;
});

$ShitBot = new GroupBot\Base\GroupBot();
