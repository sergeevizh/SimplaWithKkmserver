<?php
/**
 * Модуль для работы с kkmserver по протоколу обратного вызова
 * для касс, в которых используется протокол
 * ОФД 1.0 !!
 *
 * (c) http://with54fz.ru/ , email: main@with54fz.ru
 */
// -------------------------------------------------------------------------------
//  1. сперва копируем этот файл выше как callback.php
// -------------------------------------------------------------------------------
//  dist/callback.dist.php - пример из поставки модуля
//  dist/callback.php - под этим именем будет ваша резерная копия настроенного файла
// --------------------------------------------------------------------------------
ob_start();
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set("display_errors", 1);
ini_set("html_errors", 0);

// --------------------------------------------------------------------------------
//  2. Правим на свой емайл куда слать ошибки
// --------------------------------------------------------------------------------
define('ERROR_EMAIL', 'support@simplacms54fz.ru'); // кому
define('EMAIL_FROM', 'kkm@simplacms54fz.ru'); // от кого
define('EMAIL_TRANSPORT', 'SMTP'); // SMTP or MAIL or TELEGRAM
// если smtp
define('SMTP_HOST','mail.simplacms54fz.ru');
define('SMTP_PORT',465);
define('SMTP_SSL',true);
define('SMTP_USER','kkm@simplacms54fz.ru'); // учетка для from
define('SMTP_PASSWORD','********');
define('SMTP_DEBUG',0) ;
// telegram
define('BOT_TOOKEN','');
define('BOT_CHANNEL','');
// --------------------------------------------------------------------------------
// 3. тестируем , что пришло письмо об отсутвии config.php
// ===============================================================================
require_once('src/KkmCallback.php');
require_once('src/PHPFatalError.php');
PHPFatalError::setHandler();

// его пока нет в рабочей папке, на следующем шаге настройки
// копируем из dist/config.dist.php как ./co
require_once('config.php');

// если нет функции в конфиге или отладка в файл отключена
if (!function_exists('debuglog')) {
    function debuglog($text)
    {
        // заглушка
    }
}

$run = new KkmCallback();
$run->run();

// ----------------------------------------------------------------------------------
// 4. Копируем настроенный и проверенный в dist
// ----------------------------------------------------------------------------------

// ----------------------------------------------------------------------------------
// 5. Переходим к настройке config.php
// ----------------------------------------------------------------------------------
