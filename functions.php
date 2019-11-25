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
date_default_timezone_set('Europe/Moscow');

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
function isCorrectLength(string $str, int $min, int $max)
{
    $str = checkUserData($str);
    if (mb_strlen($str) < $min || mb_strlen($str) > $max) {
        return "Значение поля должно быть не меньше $min и не больше $max символов";
    }
}

//Проверка корректного числа
function isCorrectNumber(int $num, int $min, int $max)
{
    $num = filter_var($num, FILTER_VALIDATE_INT);
    if ($num < $min || $num > $max) {
        return "Введите значение от $min до $max";
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
        (!preg_match("/^[0-9a-zA-Zа-яА-Я]+$/", $pass)) ? $result = "Пароль должен содержать только буквы и цифры" : '';

    }

    if (isset($result)) {
        return $result;
    }
}



