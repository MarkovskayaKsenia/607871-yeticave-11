<?php
require_once('helpers.php');
require_once('functions/general.php');
require_once('functions/validation.php');
require_once('functions/config.php'); //Настройки подключения к базе данных

//Проверка авторизации юзера
if (isset($_SESSION['user'])) {
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
    $required_fields = ['email', 'password'];

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
            return isCorrectPassword($_POST['password'], $ranges['password_min'], $ranges['password_max']);
        },
    ];

    $errors = validationFormFields($_POST, $required_fields, $rules, $empty_errors, $ranges);

    if (count($errors) === 0) {
        //Проверка на существование пользователя с таким  email
        $email = mysqli_real_escape_string($mysql, $_POST['email']);
        $sql_email_query = "SELECT * FROM users WHERE email = '$email'";
        $result_user = mysqli_query($mysql, $sql_email_query);

        if (mysqli_num_rows($result_user) === 1) {
            $user = mysqli_fetch_assoc($result_user);

            if (password_verify($_POST['password'], $user['password'])) {
                $_SESSION['user'] = $user;
                header('Location: /');
                exit();
            } else {
                $errors['password'] = 'Неверный пароль';
            }

        } else {
            $errors['email'] = 'Пользователя с таким email в базе не существует';
        }
    }
}

//Заголовок страницы
$title = 'Вход';

//Заполнение шаблонов данными и вставка на старницу
$outfit_navigation = include_template('outfit-nav.php', ['outfit_categories' => $outfit_categories]);

$page_content = include_template('login.php', [
    'outfit_navigation' => $outfit_navigation,
    'errors' => $errors,
]);

$layout_content = include_template('layout.php', [
    'content' => $page_content,
    'outfit_navigation' => $outfit_navigation,
    'title' => $title,
]);

print($layout_content);
