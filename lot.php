<?php
require_once ('helpers.php');
require_once ('functions.php');
require_once('config.php'); //Настройки подключения к базе данных

//Очистка данных, переданных в $_GET
$lot_id = $_GET['id'] ?? 0;
$lot_id = intval(filter_var($lot_id, FILTER_VALIDATE_INT));

//Получение категории из базы данных
$sql_categories = "SELECT id, description FROM outfit_categories";
$result_categories = mysqli_query($mysql, $sql_categories);

//Получение лота из базы данных
$sql_lot = "SELECT ul.id AS id, ul.user_id AS user_id, outfit_title, img_url, expiry_date, bid_step, "
    . "ul.description AS description, oc.description AS outfit_category, count(lb.bid_amount) as bids_count, "
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

//Заполняем шаблон навигации сайта по категориям
$outfit_nav = include_template('outfit-nav.php', ['outfit_categories' => $outfit_categories]);

//Проверка на количество полученных лотов
if ($lots_count == 0) {
    header($_SERVER['SERVER_PROTOCOL'] . '404 Not Found');

    //Заголовок старницы 404
    $title = '404';
    //Контент страницы 404
    $page_content = include_template('404.php', [
        'outfit_nav' => $outfit_nav,
    ]);
} else {
    $lot_data = mysqli_fetch_assoc($result_lot);

    //Получение истории ставок для лота
    $sql_bids = "SELECT lb.id AS id, lb.reg_date AS reg_date, lb.user_id AS user_id, bid_amount, login "
        . "FROM lots_bids AS lb LEFT JOIN users ON lb.user_id = users.id "
        . "WHERE lb.lot_id = '$lot_id' "
        . "ORDER BY lb.reg_date DESC";

    $result_bids = mysqli_query($mysql, $sql_bids);
    $bids_count = mysqli_num_rows($result_bids);
    $bids_list = mysqli_fetch_all($result_bids, MYSQLI_ASSOC);

    //Валидация ставки на лот
    //Массив для сбора ошибок валидации
    $errors = [];

    require_once ('bid-validation.php');

    //Заголовок старницы в случае существования лота
    $title = $lot_data['outfit_title'];
    //Расчет срока окончания торгов для лота
    $expiry_time = countExpiryTime($lot_data['expiry_date']);
    //Контент страницы в случае существования лота
    $page_content = include_template('lot-card.php', [
        'outfit_nav' => $outfit_nav,
        'lot_data' => $lot_data,
        'expiry_time' => $expiry_time,
        'bids_count' => $bids_count,
        'bids_list' => $bids_list,
        'errors' => $errors,
    ]);
}

$layout_content = include_template('layout.php', [
    'content' => $page_content,
    'outfit_nav' => $outfit_nav,
    'title' => $title,
]);

print_r($layout_content);


