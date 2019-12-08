<?php
require_once('helpers.php');
require_once('functions/functions.php');
require_once('functions/validation.php');
require_once('config.php'); //Настройки подключения к базе данных

//Проверка авторизации юзера
if (!isset($_SESSION['user'])) {
    header($_SERVER['SERVER_PROTOCOL'] . '403 Forbidden');
    header('Location: /');
    die();
}

//Получение категории из базы данных
$sql_categories = "SELECT id, name, description FROM outfit_categories";
$result_categories = mysqli_query($mysql, $sql_categories);

if (!$result_categories) {
    $error = mysqli_error($mysql);
    print ("Ошибка MySQL: " . $error);
    die();
}

$outfit_categories = mysqli_fetch_all($result_categories, MYSQLI_ASSOC);

//Массив для сбора ошибок валидации
$errors = [];

//Валидация формы добавления нового лота
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //Массив полей, обязательных к заполнению
    $required_fields = ['lot-name', 'category', 'message', 'lot-rate', 'lot-step', 'lot-date'];

    //Текст ошибок для пустых полей формы
    $empty_errors = [
        'lot-name' => 'Введите наименование лота',
        'category' => 'Выберите категорию',
        'message' => 'Напишите описание лота',
        'lot-rate' => 'Введите начальную цену',
        'lot-step' => 'Введите шаг ставки',
        'lot-date' => 'Введите дату завершения торгов',
    ];

    //Массив допустимых диапазонов для полей формы
    $ranges = [
        'lot-name_min' => 20,
        'lot-name_max' => 100,
        'category' => ['outfit_categories' => $outfit_categories, 'empty_errors' => $empty_errors],
        'message_min' => 20,
        'message_max' => 5000,
        'lot-rate_min' => 1,
        'lot-rate_max' => 100000,
        'lot-step_min' => 1,
        'lot-step_max' => 100000,
        'lot-date_min' => date('Y-m-d', strtotime("+1 day")),
        'lot-date_max' => date('Y-m-d', strtotime("+1 month")),
    ];

    //Правила валидации для полей
    $rules = [
        'lot-name' => function (array $ranges) {
            return isCorrectLength($_POST['lot-name'], $ranges['lot-name_min'], $ranges['lot-name_max']);
        },
        'category' => function (array $ranges) {
            return checkCategoryExistence($ranges['category']['outfit_categories'], $ranges['category']['empty_errors'],
                $_POST['category']);
        },
        'message' => function (array $ranges) {
            return isCorrectLength($_POST['message'], $ranges['message_min'], $ranges['message_max']);
        },
        'lot-rate' => function (array $ranges) {
            return isCorrectNumber($_POST['lot-rate'], $ranges['lot-rate_min'], $ranges['lot-rate_max']);
        },
        'lot-step' => function (array $ranges) {
            return isCorrectNumber($_POST['lot-step'], $ranges['lot-step_min'], $ranges['lot-step_max']);
        },
        'lot-date' => function (array $ranges) {
            return isCorrectDate($_POST['lot-date'], $ranges['lot-date_min'], $ranges['lot-date_max']);
        },
    ];

    $errors = validationFormFields($_POST, $required_fields, $rules, $empty_errors, $ranges);

    //Проверка на ошибки при загрузке изображения лота
    if (checkLotImg($_FILES['lot-img'])) {
        $errors['lot-img'] = checkLotImg($_FILES['lot-img']);
    };

    //Загрузка лота в базу данных
    if (count($errors) === 0) {
        require_once('load-lot-db.php');
    }
}

//Отрисовка страницы
//Заголовок страницы
$title = 'Добавление лота';

//Флаг подключения стилевого файла для поля "Дата окончания торгов"
$flatpickr = true;

//Заполнение шаблонов данными и вставка на старницу
$outfit_navigation = include_template('outfit-nav.php', ['outfit_categories' => $outfit_categories]);

$page_content = include_template('add-lot.php', [
    'outfit_navigation' => $outfit_navigation,
    'outfit_categories' => $outfit_categories,
    'errors' => $errors,
]);

$layout_content = include_template('layout.php', [
    'content' => $page_content,
    'outfit_navigation' => $outfit_navigation,
    'title' => $title,
    'flatpickr' => $flatpickr,
]);

print($layout_content);

