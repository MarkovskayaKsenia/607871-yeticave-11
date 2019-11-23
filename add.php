<?php
require_once ('helpers.php');
require_once ('data.php');
require_once ('functions.php');
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

//Массив допустимых диапазонов для полей формы
$ranges = [
    'lot-name_min' => 20,
    'lot-name_max' => 100,
    'message_min' => 20,
    'message_max' => 5000,
    'lot-rate_min' => 3500,
    'lot-rate_max' => 100000,
    'lot-step_min' => 10,
    'lot-step_max' => 100000,
    'lot-date_min' => date('Y-m-d', strtotime("+1 day")),
    'lot-date_max' => date('Y-m-d', strtotime("+1 month")),
];

//Валидация формы добавления нового лота
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    /*$post = [
        'lot-name' => 'Шапка-пупырка the best of the world',
        'category' => 'Ботинки',
        'message' => 'Самая крутая шапка на земле!!!!',
        'lot-rate' => 4000,
        'lot-step' => 20,
        'lot-date' => '2019-11-24',
    ];*/

    //Массив полей, обязательных к заполнению
    $required_fields = ['lot-name', 'category', 'message', 'lot-rate', 'lot-step', 'lot-date'];
    //Идентификатор выбранной категории лота
    $category_id = 0;

    //Правила валидации для полей
    $rules = [
        'lot-name' => function () {
            global $ranges;
            return isCorrectLength($_POST['lot-name'], $ranges['lot-name_min'], $ranges['lot-name_max']);
        },
        'category' => function () {
            return checkCategoryExistence($_POST['category']);
        },
        'message' => function () {
            global $ranges;
            return isCorrectLength($_POST['message'], $ranges['message_min'], $ranges['message_max']);
        },
        'lot-rate' => function () {
            global $ranges;
            return isCorrectNumber($_POST['lot-rate'], $ranges['lot-rate_min'], $ranges['lot-rate_max']);
        },
        'lot-step' => function () {
            global $ranges;
            return isCorrectNumber($_POST['lot-step'], $ranges['lot-step_min'], $ranges['lot-step_max']);
        },
         'lot-date' => function () {
        global $ranges;
        return isCorrectDate($_POST['lot-date'], $ranges['lot-date_min'], $ranges['lot-date_max']);
         },
    ];

    //Текст ошибок для пустых полей формы
    $empty_errors = [
        'lot-name' => 'Введите наименование лота',
        'category' => 'Выберите категорию',
        'message' => 'Напишите описание лота',
        'lot-rate' => 'Введите начальную цену',
        'lot-step' => 'Введите шаг ставки',
        'lot-date' => 'Введите дату завершения торгов',
    ];

    //Массив для сбора ошибок валидации
    $errors = [];

    //Проверка корректной категории добавляемого лота
    function checkCategoryExistence($str) {
        global $category_id, $outfit_categories, $empty_errors;
        $str = checkUserData($str);
        foreach ($outfit_categories as $value) {
            if ($value['description'] == $str) {
                $category_id = $value['id'];
                break;
            }
        }
        if ($category_id == 0) {
            return $empty_errors['category'];
        }
    }

    //Проверка корректной даты окончания лота
    function isCorrectDate($date, $min, $max) {
       if (!is_date_valid($date)) {
           return $result = 'Формат даты ГГГГ-ММ-ДД';
       } else if( $date < $min || $date > $max) {
           return $result = "Введите значение от $min до $max";
       }
    }

    foreach ($_POST as $key => $value) {

        if (isset($value) && !empty($value)) {
            $result = $rules[$key]();
        } else {
            $result = (in_array($key, $required_fields)) ? $empty_errors[$key] : '';
        }

        (isset($result) && !empty($result)) ? $errors[$key] = $result : '';
    };
}


//Заголовок страницы
$title = 'Добавление лота';

//Флаг подключения стилевого файла для поля "Дата окончания торгов"
$flatpickr = true;

//Заполнение шаблонов данными и вставка на старницу
$page_content = include_template('add-lot.php', [
    'outfit_categories' => $outfit_categories,
    'ranges' => $ranges,
    'errors' => $errors,
    //'post' =>$_post
]);

$layout_content = include_template('layout.php', [
    'content' => $page_content,
    'outfit_categories' => $outfit_categories,
    'user_name' => $user_name,
    'is_auth' => $is_auth,
    'title' => $title,
    'flatpickr' => $flatpickr,
]);

print($layout_content);
