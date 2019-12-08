<?php
require_once ('helpers.php');
require_once('functions/functions.php');
require_once('config.php'); //Настройки подключения к базе данных
require_once('getwinner.php');

//Запрос на получение списка категорий лотов
$sql_categories = "SELECT id, name, description FROM outfit_categories";
$result_categories = mysqli_query($mysql, $sql_categories);

//Запрос на получение массива объявлений о продаже
$sql_adverts = "SELECT ul.id AS id, outfit_title, img_url, expiry_date, oc.description AS outfit_category, count(lb.bid_amount) AS bid_count, "
        . "IF (count(lb.bid_amount) > 0, MAX(lb.bid_amount), ul.starting_price) AS price "
        . "FROM users_lots AS ul "
        . "LEFT JOIN outfit_categories AS oc ON ul.outfit_category_id = oc.id "
        . "LEFT JOIN lots_bids AS lb ON ul.id = lb.lot_id "
        . "WHERE expiry_date > NOW() "
        . "GROUP BY ul.id ORDER BY ul.reg_date DESC";

$result_adverts = mysqli_query($mysql, $sql_adverts);

if (!$result_adverts || !$result_categories) {
    $error = mysqli_error($mysql);
    print ("Ошибка MySQL: " . $error);
    die();
}

$sale_adverts = mysqli_fetch_all($result_adverts, MYSQLI_ASSOC);
$outfit_categories = mysqli_fetch_all($result_categories, MYSQLI_ASSOC);

//Заголовок страницы
$title = 'Главная';

//Расчет срока окончания торгов для всех объявлений
$expiry_times = array_map('countExpiryTime', (array_column($sale_adverts, 'expiry_date')));

//Массив для множественного склонения слова 'ставка'
$bids_declension = ['ставка', 'ставки', 'ставок'];

//Заполнение шаблонов данными и вставка на старницу
$outfit_navigation = include_template('outfit-nav.php', ['outfit_categories' => $outfit_categories]);

$adverts_block = include_template('ads-block.php', [
    'sale_adverts' => $sale_adverts,
    'expiry_times'=> $expiry_times,
    'bids_declension' => $bids_declension,
]);

$page_content = include_template('main.php', [
    'outfit_categories' => $outfit_categories,
    'adverts_block' => $adverts_block,
    ]);

$layout_content = include_template('layout.php', [
    'content' => $page_content,
    'outfit_navigation' => $outfit_navigation,

    'title' => $title,
    ]);

print($layout_content);

