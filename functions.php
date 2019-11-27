<?php
//Функция форматирования суммы на разряды.
function formatPrice(float $num): string
{
    $price = ceil($num);
    $newFormat = number_format($price, 0, '.', ' ');
    $ruble_sign = '&#x20bd';
    $price = ($price < 1000) ? $price : $newFormat;
    return $price . ' ' . $ruble_sign;
}

//Функция проверки и очистки данных, введенных пользователем.
function checkUserData(string $str): string
{
    return htmlspecialchars(strip_tags(trim($str)));
}

//Функция расчета срока окончания торгов
function countExpiryTime(string $date): array
{
    $expiry_date = date_create($date);
    $now = date_create(date('Y-m-d H:i:s'));

    if ($expiry_date > $now) {
        $diff = (array)date_diff($now, $expiry_date);
        $hours_in_day = 24;
        $hours_left = $diff['d'] * $hours_in_day + $diff['h'];
        $hours_left = ($hours_left >= 10) ? ('' . $hours_left) : ('0' . $hours_left);
        $minutes_left = ($diff['i'] >= 10) ? ('' . $diff['i']) : ('0' . $diff['i']);
        $time_left = [$hours_left, $minutes_left];
    } else {
        $time_left = ['00', '00'];
    }

    return $time_left;
}

//Проверка существования данных в массиве $_POST
function getFormData(array $arr, string $name): string
{
    return $arr[$name] ?? '';
}

//Проверка корректной длины строки
function isCorrectLength($str, int $min, int $max)
{
    $str = checkUserData($str);
    if (mb_strlen($str) < $min || mb_strlen($str) > $max) {
        return "Значение поля должно быть не меньше $min и не больше $max символов";
    }
}

//Проверка корректного числа
function isCorrectNumber($num, int $min, int $max)
{
    $num = filter_var($num, FILTER_VALIDATE_INT);
    if (isset($num) && !empty($num)) {
        if ($num < $min || $num > $max) {
            $result = "Введите значение от $min до $max";
        }
    } else {
        $result = "Введите корректное число";
    }

    if(isset($result)) {
        return $result;
    }
}

//Проверка загружаемого изображения
function checkLotImg(array $img)
{
    if ($img['error'] != UPLOAD_ERR_NO_FILE) {
        $file_type = mime_content_type($img['tmp_name']);
        $allowed_mime = ['image/jpg', 'image/png', 'image/jpeg'];

        if (!in_array($file_type, $allowed_mime)) {
            $result = 'Загрузите файл с расширением jpg или png!';
        } elseif ($img['error'] != UPLOAD_ERR_OK) {
            $result = 'Ошибка при загрузке файла: UPLOAD_ERR_OK';
        } elseif ($img['size'] > 5 * pow(10, 6)) {
            $result = 'Файл не должен превышать 50 Mb';
        }

    } else {
        $result = 'Загрузите файл с изображением лота';
    }

    if (isset($result)) {
        return $result;
    };
}

//Проверка корректной даты окончания лота
function isCorrectDate($date, $min, $max)
{
    if (!is_date_valid($date)) {
        $result = 'Формат даты ГГГГ-ММ-ДД';
    } elseif ($date < $min || $date > $max) {
        $result = "Введите значение от $min до $max";
    }

    if (isset($result)) {
        return $result;
    }
}

//Проверка корректной категории добавляемого лота
function checkCategoryExistence(array $outfit_categories, array $empty_errors, $category_id)
{
    $category_id = (isset($category_id) && filter_var($category_id, FILTER_VALIDATE_INT)) ? $category_id : 0;
    if (!in_array($category_id, array_column($outfit_categories, 'id'))) {
        return $result = $empty_errors['category'];
    }
}

//Генерация нового имени файла
function getRandomFileName(string $path, string $filename)
{
    $extension = pathinfo($filename, PATHINFO_EXTENSION);

    do {
        $name = uniqid();
        $file = $path . $name . '.' . $extension;
    } while (file_exists($file));

    return $name . '.' . $extension;
}

//Проверка корректности email
function isCorrectEmail(string $str) {
    if(!filter_var($str, FILTER_VALIDATE_EMAIL)) {
        return 'Введите корректный email';
    }
}


//Проверка корректности пароля
function isCorrectPassword($pass, $min, $max)
{
    $pass = trim($pass);
    $errorLength = isCorrectLength($pass, $min, $max);
    if (isset($errorLength)) {
        $result = $errorLength;
    } else {
        (preg_match("/^[0-9a-zA-Zа-яА-Я]+$/", $pass) !== 1) ? $result = "Пароль должен содержать только буквы и цифры" : '';

    }

    if (isset($result)) {
        return $result;
    }
}

//Функция для проверки расхождения ассоциативных массивов с одинаковыми ключами
function key_compare_func($key1, $key2)
{
    if ($key1 == $key2)
        return 0;
    else if ($key1 > $key2)
        return 1;
    else
        return -1;
}

//Склонение единиц измерения числа, данные в массиве: [для числа 1, для чисел 2-4, для остальных чисел]
function declensionOfNouns(int $num, array $nouns): string {

    if ($num >= 11 and $num <= 14) {
        $i = 2;
    } else {
        switch ($num % 10){
            case 1: $i = 0;
                break;
            case 2:
            case 3:
            case 4: $i = 1;
                break;
            default: $i = 2;
                break;
        }
    }

    return $num . ' ' . $nouns[$i];
}

//Подбор формата для отображения "срока давности" даты
function formatTimeDistance(string $date): string {

    $reg_date = strtotime($date);
    $now = strtotime(date('Y-m-d H:i:s'));
    $diff = $now - $reg_date;

    $time_declensions = [
        'hours' => ['час', 'часа', 'часов'],
        'minutes' => ['минуту', 'минуты', 'минут']
    ];

    $diff_distance = [
        'days' => floor($diff / 86400),
        'hours' => floor($diff / 3600),
        'minutes' => floor($diff / 60)
    ];

    if($diff_distance['days'] > 0) {
        $date_format= date('y.m.d',$reg_date) . ' в ' . date('H:i', $reg_date);
    } else{
        $key = ($diff_distance['hours'] > 0) ? 'hours' : 'minutes';
        $num_display = $diff_distance[$key];
        $format = $time_declensions[$key];
    }

    $result = (isset($num_display)) ? declensionOfNouns($num_display, $format) . ' назад': $date_format;
    return $result;
}

//Проверка на право сделать ставку - доступ к кнопке на форме
function bidResolution($lot_data, $bids_list) {
    //Идентификатор последней ставки
    $last_bid_id = (!empty($bids_list)) ? intval(max(array_column($bids_list, 'id'))) : 0;
    //Ищем юзера с последней ставкой
    $potential_winner = 0;
    foreach ($bids_list as $value) {
        if($value['id'] == $last_bid_id) {
            $potential_winner = $value['user_id'];
        }
    }
    //Правила проверки
    $rules =[
        'date' =>  ($lot_data['expiry_date'] > date('Y-m-d H:i:s')) ? true : false,
        'session'=> (isset($_SESSION['user']) && $lot_data['user_id'] != $_SESSION['user']['id']) ? true : false,
        'last_bid' => (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] != $potential_winner) ? true : false,
    ];

    //Право на ставку есть, если не нарушено ни одно из правил
    $result  = true;
    foreach($rules as $value) {
        if ($value !== true) {
            $result = false;
        }
    }
    return $result;
}






