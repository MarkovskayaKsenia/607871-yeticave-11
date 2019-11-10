<?php
require_once ('helpers.php');
require_once ('data.php');
require_once ('functions.php');

$mysql = mysqli_connect('localhost', 'ksenia', 'thesimpsons', 'yeticave');
mysqli_set_charset($mysql, 'utf8');

$lot_id = (int) $_GET['id'];
//print $lot_id;
if (!$mysql) {
    print ('Ошибка подключения: ' . mysqli_connect_error());
} else {
    $sql_categories = "SELECT description FROM outfit_categories";
    $result_categories = mysqli_query($mysql, $sql_categories);

    $sql_lot = "SELECT outfit_title, img_url, expiry_date, bid_step, ul.description AS description, "
        . "oc.description AS outfit_category, count(lb.bid_amount) AS bid_count, "
        . "IF (count(lb.bid_amount) > 0, MAX(lb.bid_amount), ul.starting_price) as price "
        . "FROM users_lots AS ul "
        . "LEFT JOIN outfit_categories AS oc ON ul.outfit_category_id = oc.id "
        . "LEFT JOIN lots_bids AS lb ON ul.id = lb.lot_id "
        . "WHERE ul.id = " . $lot_id;
    $result_lot = mysqli_query($mysql, $sql_lot);

    $sql_bids = "SELECT lb.reg_date, bid_amount, login FROM lots_bids AS lb "
        . "LEFT JOIN users ON lb.user_id = users.id "
        . "WHERE lb.lot_id = " . $lot_id
        . " ORDER BY lb.reg_date DESC";

    $result_bids = mysqli_query($mysql, $sql_bids);

    if (!$result_categories || !$result_lot) {
        $error = mysqli_error($mysql);
        print ('Ошибка MySQL: ' . $error);
    } else {
        $outfit_categories = mysqli_fetch_all($result_categories, MYSQLI_ASSOC);
        $lot_data = mysqli_fetch_assoc($result_lot);
        $bids_list = mysqli_fetch_all($result_bids, MYSQLI_ASSOC);
    }
}
//Заголовок старницы
$title = $lot_data['outfit_title'];
//Расчет срока окончания торгов для лота
$expiry_time = countExpiryTime($lot_data['expiry_date']);

//Заполнение шалонов данными и вставка на страницу
$page_content = include_template('lot-card.php', [
    'outfit_categories' => $outfit_categories,
    'lot_data' => $lot_data,
    'expiry_time' => $expiry_time,
    'bids_list' => $bids_list,
]);

$layout_content = include_template('layout.php', [
    'content' => $page_content,
    'outfit_categories' => $outfit_categories,
    'user_name' => $user_name,
    'is_auth' => $is_auth,
    'title' => $title,
    'main_class' => '',
]);

print_r($layout_content);

