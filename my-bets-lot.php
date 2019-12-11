<?php
require_once('helpers.php');
require_once('functions/general.php');
require_once('functions/config.php'); //Настройки подключения к базе данных

//Проверка авторизации юзера
if (!isset($_SESSION['user'])) {
    header($_SERVER['SERVER_PROTOCOL'] . '403 Forbidden');
    header('Location: /');
    die();
}

$user_id = $_SESSION['user']['id'];

//Запрос на получение списка категорий лотов
$sql_categories = "SELECT id, name, description FROM outfit_categories";
$result_categories = mysqli_query($mysql, $sql_categories);

//Запрос на получение массива объявлений о продаже
$sql_ads = "SELECT ul.id AS id, (SELECT users.contacts FROM users WHERE users.id = ul.user_id) AS contacts, "
    . "outfit_title, img_url, expiry_date, oc.description AS outfit_category, ul.winner_id AS winner_id, "
    . "lb.reg_date AS bid_date, lb.bid_amount AS bid, "
    . "(SELECT MAX(bid_amount) FROM lots_bids WHERE lb.lot_id = lots_bids.lot_id group by lots_bids.lot_id ) AS max_bid "
    . "FROM users AS u "
    . "INNER JOIN lots_bids AS lb ON u.id = lb.user_id "
    . "LEFT JOIN users_lots AS ul ON lb.lot_id = ul.id "
    . "LEFT JOIN outfit_categories AS oc ON ul.outfit_category_id = oc.id "
    . "WHERE u.id = ? ORDER BY bid_date DESC ";

$stm_ads = db_get_prepare_stmt($mysql, $sql_ads, [$user_id]);
$execute_ads = mysqli_stmt_execute($stm_ads);

if (!$execute_ads || !$result_categories) {
    $error = mysqli_error($mysql);
    print ("Ошибка MySQL: " . $error);
    die();
}

$result_ads = mysqli_stmt_get_result($stm_ads);
$count_ads = mysqli_num_rows($result_ads);
$outfit_categories = mysqli_fetch_all($result_categories, MYSQLI_ASSOC);

//Заполнение шаблона навигации по категориям
$outfit_navigation = include_template('outfit-nav.php', ['outfit_categories' => $outfit_categories]);

if ($count_ads == 0) {
    //Заполнение шалона контента страницы в случае отсутствия найденных лотов
    $page_content = include_template('empty-search.php', ['outfit_navigation' => $outfit_navigation]);
} else {
    //Зполннеие шаблона в случае найденных лотов
    $sale_ads = mysqli_fetch_all($result_ads, MYSQLI_ASSOC);

    //Расчет срока окончания торгов для всех объявлений
    $expiry_times = array_map('countExpiryTime', (array_column($sale_ads, 'expiry_date')));
    $bargain_status = [];

   //Вычисление статуса торгов
    foreach($expiry_times as $key => $value){
        $bargain_status[] = checkBargainStatus(
            $value,
            $sale_ads[$key]['winner_id'],
            $user_id,
            $sale_ads[$key]['bid'],
            $sale_ads[$key]['max_bid']
        );
    }

    //Заполнение контента страницы
    $page_content = include_template('my-bets.php', [
        'outfit_navigation' => $outfit_navigation,
        'sale_ads' => $sale_ads,
        'expiry_time' => $expiry_times,
        'bargain_status' => $bargain_status,
    ]);
}

//Заголовок страницы
$title = 'Мои ставки';

//Заполнение шаблонов данными и вставка на старницу

$layout_content = include_template('layout.php', [
    'content' => $page_content,
    'outfit_navigation' => $outfit_navigation,
    'title' => $title,
]);

print($layout_content);

