<?php
require_once ('helpers.php');
require_once ('data.php');
require_once('functions.php');

$mysql = mysqli_connect('localhost', 'ksenia', 'thesimpsons', 'yeticave');
mysqli_set_charset($mysql, 'utf8');

if (!$mysql) {
    print ('Ошибка подключения: ' . mysqli_connect_error());
} else {
    //Запрос на получение массива объявлений о продаже
    $sql_ads = "SELECT ul.id AS id, outfit_title, img_url, expiry_date, oc.description AS outfit_category, count(lb.bid_amount) AS bid_count, "
        . "IF (count(lb.bid_amount) > 0, MAX(lb.bid_amount), ul.starting_price) as price "
        . "FROM users_lots AS ul "
        . "LEFT JOIN outfit_categories AS oc ON ul.outfit_category_id = oc.id "
        . "LEFT JOIN lots_bids AS lb ON ul.id = lb.lot_id "
        . "WHERE expiry_date > NOW() "
        . "GROUP BY ul.id ORDER BY ul.reg_date DESC";

    $result_ads = mysqli_query($mysql, $sql_ads);

    //Запрос на получение списка категорий лотов
    $sql_categories = "SELECT name, description FROM outfit_categories";
    $result_categories = mysqli_query($mysql, $sql_categories);

    if (!$result_ads || !$result_categories) {
        $error = mysqli_error($mysql);
        print ("Ошибка MySQL: " . $error);
    } else {
        $sale_ads = mysqli_fetch_all($result_ads, MYSQLI_ASSOC);
        $outfit_categories = mysqli_fetch_all($result_categories, MYSQLI_ASSOC);
    }
}
//Заголовок страницы
$title = 'Главная';

//Расчет срока окончания торгов для всех объявлений
$expiry_time = array_map('countExpiryTime', (array_column($sale_ads, 'expiry_date')));

//Заполнение шаблонов данными и вставка на старницу
$page_content = include_template('main.php', [
    'outfit_categories' => $outfit_categories,
    'sale_ads' => $sale_ads,
    'expiry_time'=> $expiry_time,
    ]);

$layout_content = include_template('layout.php', [
    'content' => $page_content,
    'outfit_categories' => $outfit_categories,
    'user_name' => $user_name,
    'is_auth' => $is_auth,
    'title' => $title,
    ]);

print($layout_content);

