<?php

spl_autoload_register( function ($class) {
    $include_path = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists(__DIR__ . '/' . $include_path))
        return include(__DIR__ . '/' . $include_path);
    else
        return false;
});


function generateKeyboard()
{
    $height = mt_rand(1,6);
    $width = mt_rand(1,6);
    $button = mt_rand(0, $height * $width - 1);

    $keyboard = [];
    for ($i = 0; $i < $height; $i++) {
        $row = [];
        for ($j = 0; $j < $width; $j++) {
            if ($i * $width + $j == $button) {
                $row[] = [
                    'text' => 'YES',
                    'callback_data' => "/click button"
                ];
            } else {
                $row[] = [
                    'text' => 'NO',
                    'callback_data' => ""
                ];
            }
        }
        $keyboard[] = $row;
    }
    return $keyboard;
}

print_r(generateKeyboard());