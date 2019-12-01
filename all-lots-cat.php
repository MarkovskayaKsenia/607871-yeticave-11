<?php
require_once('helpers.php');
require_once('functions.php');
require_once('config.php'); //Настройки подключения к базе данных

$category = getFormData($_GET, 'category');
$category = checkUserData($category);

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    header('Location: /');
    die();
}

//Запрос на получение списка категорий лотов
$sql_categories = "SELECT id, name, description FROM outfit_categories";
$result_categories = mysqli_query($mysql, $sql_categories);

//Запрос на количество лотов в базе
$sql_count = "SELECT COUNT(*) as cnt FROM users_lots WHERE expiry_date > NOW() AND outfit_category_id = ?";
$stm_count = db_get_prepare_stmt($mysql, $sql_count, [$category]);
$exec_count = mysqli_stmt_execute($stm_count);
$result_count = mysqli_stmt_get_result($stm_count);
$rows_count = mysqli_fetch_assoc($result_count)['cnt'];

//Параметры пагинации
$cur_page = $_GET['page'] ?? 1;
$cur_page = (intval(filter_var($cur_page, FILTER_VALIDATE_INT))) ?? 1;

$page_items = 2;
$pages_count = ceil($rows_count / $page_items);
$offset_ads = ($cur_page - 1) * $page_items;
$pages = range(1, $pages_count);

//Собираем url-адрес для кнопок пагинации
$params = [
    'category' => checkUserData(getFormData($_GET, 'category')),
];
$scriptname = pathinfo(__FILE__, PATHINFO_BASENAME);
$build_query = http_build_query($params);
$url = '/' . $scriptname . '?' . $build_query;

//Запрос на получение массива объявлений о продаже
$sql_ads = "SELECT ul.id AS id, outfit_title, img_url, expiry_date, oc.description AS outfit_category, count(lb.bid_amount) AS bid_count, "
    . "IF (count(lb.bid_amount) > 0, MAX(lb.bid_amount), ul.starting_price) as price "
    . "FROM users_lots AS ul "
    . "LEFT JOIN outfit_categories AS oc ON ul.outfit_category_id = oc.id "
    . "LEFT JOIN lots_bids AS lb ON ul.id = lb.lot_id "
    . "WHERE expiry_date > NOW() AND ul.outfit_category_id = ? "
    . "GROUP BY ul.id ORDER BY ul.reg_date DESC LIMIT " . $page_items . " OFFSET " . $offset_ads;

$stm_ads = db_get_prepare_stmt($mysql, $sql_ads, [$category]);
$exec_ads = mysqli_stmt_execute($stm_ads);

if (!$exec_ads || !$result_categories) {
    $error = mysqli_error($mysql);
    print ("Ошибка MySQL: " . $error);
    die();
}

$result_ads = mysqli_stmt_get_result($stm_ads);
$count_ads = mysqli_num_rows($result_ads);
$outfit_categories = mysqli_fetch_all($result_categories, MYSQLI_ASSOC);

//Заполнение шаблона навигации по категориям
$outfit_nav = include_template('outfit-nav.php', ['outfit_categories' => $outfit_categories]);

if ($count_ads == 0) {
    //Заполнение шалона контента страницы в случае отсутствия найденных лотов
    $page_content = include_template('empty-search.php', ['outfit_nav' => $outfit_nav]);
} else {
    //Зполннеие шаблона в случае найденных лотов
    $sale_ads = mysqli_fetch_all($result_ads, MYSQLI_ASSOC);

    $category_desc = array_unique(array_column($sale_ads, 'outfit_category'))[0];

    //Расчет срока окончания торгов для всех объявлений
    $expiry_time = array_map('countExpiryTime', (array_column($sale_ads, 'expiry_date')));

    //Массив для множественного склонения слова 'ставка'
    $bids_declension = ['ставка', 'ставки', 'ставок'];

    //Заполнение шаблона пагинации
    $pagination = include_template('pagination.php', [
        'pages' => $pages,
        'pages_count' => $pages_count,
        'cur_page' => $cur_page,
        'url' => $url,
    ]);

    //Заполнение контента страницы
    $page_content = include_template('all-lots.php', [
        'outfit_nav' => $outfit_nav,
        'sale_ads' => $sale_ads,
        'expiry_time' => $expiry_time,
        'bids_declension' => $bids_declension,
        'pagination' => $pagination,
        'category_desc' => $category_desc,
    ]);
}

//Заголовок страницы
$title = 'Все лоты';

//Заполнение шаблонов данными и вставка на старницу

$layout_content = include_template('layout.php', [
    'content' => $page_content,
    'outfit_nav' => $outfit_nav,
    'title' => $title,
]);

print($layout_content);

