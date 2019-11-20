<?php
require_once ('helpers.php');
require_once ('data.php');
require_once ('functions.php');

$mysql = mysqli_connect('localhost', 'ksenia', 'thesimpsons', 'yeticave');
mysqli_set_charset($mysql, 'utf8');

if (!$mysql) {
    print ('Ошибка подключения: ' . mysqli_connect_error());
    die();
}

//Очистка данных, переданных в $_GET
$lot_id = $_GET['id'] ?? 0;
$lot_id = intval(filter_var($lot_id, FILTER_VALIDATE_INT));

//Получение категории из базы данных
$sql_categories = "SELECT description FROM outfit_categories";
$result_categories = mysqli_query($mysql, $sql_categories);

//Получение лота из базы данных
$sql_lot = "SELECT outfit_title, img_url, expiry_date, bid_step, ul.description AS description, "
    . "oc.description AS outfit_category, count(lb.bid_amount) as bids_count, "
    . "IF (count(lb.bid_amount) > 0, MAX(lb.bid_amount), ul.starting_price) as price "
    . "FROM users_lots AS ul "
    . "LEFT JOIN outfit_categories AS oc ON ul.outfit_category_id = oc.id "
    . "LEFT JOIN lots_bids AS lb ON ul.id = lb.lot_id "
    . "WHERE ul.id = ? "
    . "GROUP BY ul.id";

$stm_lot = db_get_prepare_stmt($mysql, $sql_lot, [$lot_id]);
mysqli_stmt_execute($stm_lot);
$result_lot = mysqli_stmt_get_result($stm_lot);

//Проверка исполнения запросов на категории и лот
if (!$result_categories || !$result_lot) {
    $error = mysqli_error($mysql);
    print ('Ошибка MySQL: ' . $error);
    die();
}
$outfit_categories = mysqli_fetch_all($result_categories, MYSQLI_ASSOC);
$lots_count = mysqli_num_rows($result_lot);

//Проверка на количество полученных лотов
if ($lots_count == 0) {
    header($_SERVER['SERVER_PROTOCOL'] . '404 Not Found');

    //Заголовок старницы 404
    $title = '404';
    //Контент страницы 404
    $page_content = include_template('404.php', [
        'outfit_categories' => $outfit_categories,
    ]);
} else {
    $lot_data = mysqli_fetch_assoc($result_lot);

    //Заголовок старницы в случае существования лота
    $title = $lot_data['outfit_title'];
    //Расчет срока окончания торгов для лота
    $expiry_time = countExpiryTime($lot_data['expiry_date']);
    //Контент страницы в случае существования лота
    $page_content = include_template('lot-card.php', [
        'outfit_categories' => $outfit_categories,
        'lot_data' => $lot_data,
        'expiry_time' => $expiry_time,
    ]);
}

$layout_content = include_template('layout.php', [
    'content' => $page_content,
    'outfit_categories' => $outfit_categories,
    'user_name' => $user_name,
    'is_auth' => $is_auth,
    'title' => $title,
]);

print_r($layout_content);

