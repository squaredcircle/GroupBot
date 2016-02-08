<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 17/11/2015
 * Time: 9:52 PM
 */

define('BOT_FRIENDLY_NAME', 'GroupBot');
define('BOT_FULL_USER_NAME', 'GroupBot');

define('BOT_TOKEN', '');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');
define('WEBHOOK_URL', '');

define('BASE_DIR', '');
define('PHOTO_DIR', BASE_DIR . 'photos');
define('BANANA_DIR', BASE_DIR . 'banana');
define('WOLFRAM_DIR', BASE_DIR . 'wolfram');

define('BOT_DB_HOST', '');
define('BOT_DB_USER', '');
define('BOT_DB_PASSWORD', '');
define('BOT_DB_NAME', '');

define('LOGGING_EPOCH', '1970-1-1 00:00:00');

define('COIN_CURRENCY_NAME', 'Coin');
define('COIN_TAXATION_BODY', 'The Taxman');
define('COIN_REDISTRIBUTION_BODY', "Robin Hood");

define("COIN_PERIODIC_TAX", 0.03);
define("COIN_TRANSACTION_TAX", 0.02);
define("COIN_REDISTRIBUTION_TAX", 0.33);

define("COIN_POOR_BONUS", 0.1);

define("COIN_CHANCE_ALL_TAX", 0.5);
define("COIN_CHANCE_WEALTHY_TAX", 0.1);
define("COIN_CHANCE_POOR_TAX", 0.1);
define("COIN_CHANCE_REDISTRIBUTE_TAX", 0.2);
define("COIN_CHANCE_REDISTRIBUTE_WEALTHIEST", 0.1);
define("COIN_CHANCE_INCREASE_VALUE", 0.05);
define("COIN_CHANCE_DECREASE_VALUE", 0.05);
define("COIN_CHANCE_RANDOM_BONUS", 0.05);
define("COIN_CHANCE_WEALTH_BONUS", 0.05);
define("COIN_CHANCE_POOR_BONUS", 0.05);

define("BLACKJACK_DAILY_FREE_BETS", 10);

define("AUTOTRANSLATE_ENABLED", 'true');
define("YANDEX_TRANSLATE_KEY", '');