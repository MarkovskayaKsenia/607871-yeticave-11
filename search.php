<?php
require_once('helpers.php');
require_once('functions/functions.php');
require_once('config.php'); //Настройки подключения к базе данных

$search = getFormData($_GET, 'search');
$search = checkUserData($search);

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || empty($search)) {
    header('Location: /');
    die();
}

//Запрос на получение списка категорий лотов
$sql_categories = "SELECT id, name, description FROM outfit_categories";
$result_categories = mysqli_query($mysql, $sql_categories);

//Запрос на количество лотов в базе
$sql_count = "SELECT COUNT(*) as lots_count FROM users_lots WHERE expiry_date > NOW() AND MATCH(outfit_title, description) AGAINST (?)";
$stm_count = db_get_prepare_stmt($mysql, $sql_count, [$search]);
$execute_count = mysqli_stmt_execute($stm_count);
$result_count = mysqli_stmt_get_result($stm_count);
$rows_count = mysqli_fetch_assoc($result_count)['lots_count'];

//Параметры пагинации
$current_page = $_GET['page'] ?? 1;
$page_items = 9;
$parameters = [
    'search' => checkUserData(getFormData($_GET, 'search')),
    'find' => checkUserData(getFormData($_GET, 'find')),
];
$script_name = pathinfo(__FILE__, PATHINFO_BASENAME);

$pagination_options = getPaginationOptions($current_page, $parameters, $page_items, $rows_count, $script_name);

//Запрос на получение массива объявлений о продаже
$sql_advert = "SELECT ul.id AS id, outfit_title, img_url, expiry_date, oc.description AS outfit_category, count(lb.bid_amount) AS bid_count, "
    . "IF (count(lb.bid_amount) > 0, MAX(lb.bid_amount), ul.starting_price) AS price "
    . "FROM users_lots AS ul "
    . "LEFT JOIN outfit_categories AS oc ON ul.outfit_category_id = oc.id "
    . "LEFT JOIN lots_bids AS lb ON ul.id = lb.lot_id "
    . "WHERE expiry_date > NOW() AND MATCH(outfit_title, ul.description) AGAINST (?) "
    . "GROUP BY ul.id ORDER BY ul.reg_date DESC LIMIT " . $page_items . " OFFSET " . $pagination_options['offset_adverts'];

$stm_adverts = db_get_prepare_stmt($mysql, $sql_advert, [$search]);
$execute_adverts = mysqli_stmt_execute($stm_adverts);

if (!$execute_adverts || !$result_categories) {
    $error = mysqli_error($mysql);
    print ("Ошибка MySQL: " . $error);
    die();
}

$result_adverts = mysqli_stmt_get_result($stm_adverts);
$count_adverts = mysqli_num_rows($result_adverts);
$outfit_categories = mysqli_fetch_all($result_categories, MYSQLI_ASSOC);

//Заполнение шаблона навигации по категориям
$outfit_navigation = include_template('outfit-nav.php', ['outfit_categories' => $outfit_categories]);

if ($count_adverts === 0) {
    //Заполнение шалона контента страницы в случае отсутствия найденных лотов
    $page_content = include_template('empty-search.php', ['outfit_navigation' => $outfit_navigation]);
} else {
    //Заполнение шаблона в случае найденных лотов
    $sale_adverts = mysqli_fetch_all($result_adverts, MYSQLI_ASSOC);

    //Расчет срока окончания торгов для всех объявлений
    $expiry_times = array_map('countExpiryTime', (array_column($sale_adverts, 'expiry_date')));

    //Массив для множественного склонения слова 'ставка'
    $bids_declension = ['ставка', 'ставки', 'ставок'];

    //Заполнение шаблона пагинации
    $pagination = include_template('pagination.php', [
        'pages' => $pagination_options['pages'],
        'pages_count' => $pagination_options['pages_count'],
        'current_page' => $pagination_options['current_page'],
        'url' => $pagination_options['url'],
    ]);

    $adverts_block = include_template('ads-block.php', [
        'sale_adverts' => $sale_adverts,
        'expiry_times'=> $expiry_times,
        'bids_declension' => $bids_declension,
    ]);

    //Заполнение контента страницы
    $page_content = include_template('search-lot.php', [
        'outfit_navigation' => $outfit_navigation,
        'adverts_block' => $adverts_block,
        'pagination' => $pagination,
    ]);
}

//Заголовок страницы
$title = 'Результаты поиска';

//Заполнение шаблонов данными и вставка на старницу

$layout_content = include_template('layout.php', [
    'content' => $page_content,
    'outfit_navigation' => $outfit_navigation,
    'title' => $title,
]);

print($layout_content);

