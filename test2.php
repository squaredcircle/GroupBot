<?php

$var = [
    [
        'hehge'=>2,
        'asdfasdfafsa'=>'sdfds'

    ],
    [
        2=>2
    ]
];

$output = print_r($var, true);
file_put_contents('/root/groupbot.log', $output, FILE_APPEND | LOCK_EX);
