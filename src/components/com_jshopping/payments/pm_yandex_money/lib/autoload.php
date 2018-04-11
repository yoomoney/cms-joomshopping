<?php

if (!defined('DS')) {
    define(DS, DIRECTORY_SEPARATOR);
}

require_once dirname(__FILE__) . DS . 'yandex-checkout-sdk/lib' . DS . 'autoload.php';

define('YANDEX_MONEY_MODULE_PATH', dirname(__FILE__));

function yandexMoneyLoadClass($className)
{
    if (strncmp('YandexMoney', $className, 11) === 0) {
        $path = YANDEX_MONEY_MODULE_PATH;
        $length = 11;
    } else {
        return;
    }
    if (DIRECTORY_SEPARATOR === '/') {
        $path .= str_replace('\\', '/', substr($className, $length));
    } else {
        $path .= substr($className, $length);
    }
    $path .= '.php';
    if (file_exists($path)) {
        include $path;
    }
}

spl_autoload_register('yandexMoneyLoadClass');
