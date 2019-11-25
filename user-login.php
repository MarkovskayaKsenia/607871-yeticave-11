<?php
require_once ('helpers.php');
require_once ('data.php');
require_once('functions.php');
require_once('config.php'); //Настройки подключения к базе данных

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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //Массив полей, обязательных к заполнению
    $required_fields = ['email', 'password', 'name', 'message',];

    //Текст ошибок для пустых полей формы
    $empty_errors = [
        'email' => 'Введите e-mail',
        'password' => 'Введите пароль',
    ];

//Массив допустимых диапазонов для полей формы
    $ranges = [
        'name_min' => 3,
        'name_max' => 20,
        'password_min' => 10,
        'password_max' => 20,
    ];

//Правила валидации для полей
    $rules = [
        'email' => function () {
            return isCorrectEmail($_POST['email']);
        },
        'password' => function (array $ranges) {
            return isCorrecrPassword($_POST['password'], $ranges['password_min'], $ranges['password_max']);
        },
    ];

//Применение правил валидации к полям формы
    foreach ($_POST as $key => $value) {

        if (isset($value) && !empty($value) && isset($rules[$key])) {
            $result = $rules[$key]($ranges);
        } else {
            $result = (in_array($key, $required_fields)) ? $empty_errors[$key] : '';
        }

        (isset($result) && !empty($result)) ? $errors[$key] = $result : '';
    };

}


//Отрисовка страницы
//Заголовок страницы
$title = 'Вход';

//Заполнение шаблонов данными и вставка на старницу
$page_content = include_template('login.php', [
    'outfit_categories' => $outfit_categories,
    'errors' => $errors,
]);

$layout_content = include_template('layout.php', [
    'content' => $page_content,
    'outfit_categories' => $outfit_categories,
    'user_name' => $user_name,
    'is_auth' => $is_auth,
    'title' => $title,
]);

print($layout_content);
