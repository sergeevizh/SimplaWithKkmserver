<?php
/**
 * Simpla CMS
 *
 * Демонстрация подхода к приему платежей с учетом состояния кассового аппарата
 *
 * (c) http://with54fz.ru/ , email: main@with54fz.ru
 */

/*
 *  Проверьте, а в тех способах оплаты, что Вы используете эта функция описана
 *  и понимает ли формат ее вывода апи платежной системы ? ;)
 */
function err($text)
{
    echo <<<HTML
<html>    
<body>
{$text}
</body>
</html>
HTML;

    die();
}

/**
 *  [!] фикс времени, чтобы в базу заносилось правильное время
 */
date_default_timezone_set('Etc/GMT-3');

// Работаем в корневой директории
chdir('../../');
require_once('api/Simpla.php');

$simpla = new Simpla();

if (empty($_GET['id']))
    err('Нет нужного параметра');

// Выбирем заказ из базы
$order = $simpla->orders->get_order(intval($_GET['id']));
if (empty($order))
    err('Оплачиваемый заказ не найден');

// Нельзя оплатить уже оплаченный заказ  
if ($order->paid)
    err('Этот заказ уже оплачен');

// Выбираем из базы соответствующий метод оплаты
$method = $simpla->payment->get_payment_method(intval($order->payment_method_id));
if (empty($method))
    err("Неизвестный метод оплаты");

/**
 * [!] ЕЩЕ ОДНО МЕСТО, КОТОРОЕ ЗАБЫВАЮТ ПРОВЕРИТЬ
 */
if ($method->module !== 'TestMoney')
    err('Этот заказ помечен другим методом оплаты. Оплатить тестовыми деньгами нельзя.');


/*  --------------------------------------------------------------------------------
 *  добавлять проверку доступности кассы при вызовах типа checkOrder нет смысла.
 *  в этом случае, деньги у человека просто зависнут на карте на пару дней и
 *  он не сможет оплатить повторно
    -------------------------------------------------------------------------------- */

/*  --------------------------------------------------------------------------------
 *  На всякий случай покажу как добавить проверку кассы для оплаты
    -------------------------------------------------------------------------------- */

if (!isset($_GET['force'])) { // если мы ее не игнорируем
///////////////////////////  ПРОВЕРКА СТАТУСА КАССЫ  /////////////////////
    require_once(dirname(__FILE__) . '/../../kkmserver/src/KkmAssist.php');
    $assist = new KkmAssist();
    if (!$assist->kkm_isOnline()) {
        /*
         *   попросить повторить платеж позже
         *  смотрим в описании конкретного мерчанта.
         *  А универсально вернуть 500 ошибку - у сервера внутренняя ошибка
         *
         */
//       header('HTTP/1.1 500 Internal Server Error'); die();

        err('Оплата не возможна. Онлайн-касса не доступна.<meta http-equiv="refresh" content="5;/order/' . $order->url . '">');
    }
///////////////////////////  ПРОВЕРКА СТАТУСА КАССЫ  /////////////////////
}


//////////////////////////////////////////////////////
// СТАНДАРТНУЮ ПОМЕТКУ ОПЛАТЫ РЕКОМЕНДУЮ ДОПОЛНИТЬ  //
//////////////////////////////////////////////////////

// $simpla->orders->update_order(intval($order->id), array('paid'=>1));
// поможет перевести стрелки, по срокам Для этого мы протоколируем, когда пришло уведомление.
$simpla->orders->update_order(intval($order->id), array('paid' => 1, 'payment_date' => Date('Y-m-d H:i:s'), 'payment_details' => json_encode($_REQUEST)));


// но так как эти поля в симпле не используются и никаких стандартов нет, сохраним в таблице модуля

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//                       ДОБАВЛЯЕМ ДЛЯ РАСШИРЕНИЯ ВОЗМОЖНОСТЕЙ                                              //
//////////////////////////////////////////////////////////////////////////////////////////////////////////////

$query = $simpla->db->placehold("INSERT INTO __kkmserver SET id_order=? ", (int)$order->id);
$simpla->db->query($query);
$forFuture = array(
    'ip' => $_SERVER['REMOTE_ADDR'],
    'payment_date' => Date('Y-m-d H:i:s'),
    'payment_details' => json_encode($_REQUEST)
);
$query = $simpla->db->placehold("UPDATE __kkmserver SET ?% where id_order=? ", $forFuture, (int)$order->id);
$simpla->db->query($query);

//////////////////////////////////////////////////


//  ну а раз тут у нас демо. Вернем посетителя на страницу заказа.
echo 'Заказ оплачен<meta http-equiv="refresh" content="0;/order/' . $order->url . '">';
