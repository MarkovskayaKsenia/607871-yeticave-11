<?php
require_once('helpers.php');
require_once('data.php');
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

//Массив для сбора ошибок валидации
$errors = [];

//Валидация формы добавления нового лота
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

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
    function checkCategoryExistence($str)
    {
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
    function isCorrectDate($date, $min, $max)
    {
        if (!is_date_valid($date)) {
            return $result = 'Формат даты ГГГГ-ММ-ДД';
        } else {
            if ($date < $min || $date > $max) {
                return $result = "Введите значение от $min до $max";
            }
        }
    }

//Применение правил валидации к полям формы
    foreach ($_POST as $key => $value) {

        if (isset($value) && !empty($value)) {
            $result = $rules[$key]();
        } else {
            $result = (in_array($key, $required_fields)) ? $empty_errors[$key] : '';
        }

        (isset($result) && !empty($result)) ? $errors[$key] = $result : '';
    };
//Проверка загружаемого изображения
    function checkLotImg(array $img, $key)
    {
        global $errors;

        if ($img['error'] != UPLOAD_ERR_NO_FILE) {

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $file_type = finfo_file($finfo, $img['tmp_name']);
            $allowed_mime = ['image/jpg', 'image/png', 'image/jpeg'];
            if (!in_array($file_type, $allowed_mime)) {
                $errors[$key] = 'Загрузите файл с расширением jpg или png!';
            } elseif ($img['error'] != UPLOAD_ERR_OK) {
                $errors[$key] = 'Ошибка при загрузке файла: UPLOAD_ERR_OK';
            } elseif ($img['size'] > 5 * pow(10, 6)) {
                $errors[$key] = 'Файл не должен превышать 50 Mb';
            }

            finfo_close($finfo);
        } else {
            $errors[$key] = 'Загрузите файл с изображением лота';
        }

    }

    checkLotImg($_FILES['lot-img'], 'lot-img');

//Загрузка лота в базу данных
    if (count($errors) == 0) {
//Путь сохранения изображений.
        $newImgPath =  __DIR__ . '/uploads/';
        $newImgName = getRandomFileName($newImgPath, $_FILES['lot-img']['name']);
        $newImgSrc = $newImgPath . $newImgName;

//Загрузка файла из временной папки
        move_uploaded_file($_FILES['lot-img']['tmp_name'], $newImgSrc);

//Запрос на добавление лота в базу данных
        $sql_add = "INSERT INTO users_lots (reg_date, outfit_title, description, img_url, starting_price, expiry_date, "
            . "bid_step, user_id, outfit_category_id) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";

//Подготовка данных для передачи в базу данных
        $reg_date = date('Y-m-s H:i:s');
        $outfit_title = checkUserData($_POST['lot-name']);
        $description = checkUserData($_POST['message']);
        $img_url = 'uploads/' . $newImgName;
        $starting_price = checkUserData($_POST['lot-rate']);
        $expiry_date = checkUserData($_POST['lot-date']);
        $bid_step = checkUserData($_POST['lot-step']);
        $user_id = 3;
        $outfit_category = $category_id;

//Подготовка sql-выражения для добавления лота
        $stm_add = db_get_prepare_stmt($mysql, $sql_add, [
            $reg_date,
            $outfit_title,
            $description,
            $img_url,
            $starting_price,
            $expiry_date,
            $bid_step,
            $user_id,
            $outfit_category
        ]);

        mysqli_stmt_execute($stm_add);
        $last_lot_id = mysqli_insert_id($mysql);
        header('Location: /lot.php?id=' . $last_lot_id);
    }
}
//Отрисовка страницы
//Заголовок страницы
    $title = 'Добавление лота';

//Флаг подключения стилевого файла для поля "Дата окончания торгов"
    $flatpickr = true;

//Заполнение шаблонов данными и вставка на старницу
    $page_content = include_template('add-lot.php', [
        'outfit_categories' => $outfit_categories,
        'errors' => $errors,
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


