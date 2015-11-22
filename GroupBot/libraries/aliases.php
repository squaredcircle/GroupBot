<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 11:03 PM
 */

function checkAlias($command)
{
    $aliases = array(
        'blackjack'     => 'bj',
        'spookyecho'    => 'x',
        'zalgo'         => 'z',
        'notroll'       => 'roll',
        'start'         => 'help'
    );

    $key = array_search($command, array_keys($aliases));

    return ($key !== false) ? array_values($aliases)[$key] : false;
}