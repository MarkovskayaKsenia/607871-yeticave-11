<?php
/**
 * Проверка значения на требуемое количество символов.
 * @param string $value - значение, которое нужно проверить.
 * @param int $min - минимальное количество символов.
 * @param int $max - максимальное количество символов.
 *
 * @return - Возвращает текст ошибки, если проверки не пройдена, иначе ничего не возвращает.
 */
function isCorrectLength($value, int $min, int $max)
{
    $value = checkUserData($value);
    if (mb_strlen($value) < $min || mb_strlen($value) > $max) {
        return "Значение поля должно быть не меньше $min и не больше $max символов";
    }
}

/**
 * Проверка на то, что значение является целым числом, и входит в разрешенный диапазон.
 * @param int $number - значение, которое нужно проверить.
 * @param int $min - минимально допустимое значение числа.
 * @param int $max - максимально допустимое значение числа.
 *
 * @return - Возвращает текст ошибки, если проверки не пройдена, иначе ничего не возвращает.
 */
function isCorrectNumber($number, int $min, int $max)
{
    $number = filter_var($number, FILTER_VALIDATE_INT);
    if (!isset($number) || empty($number)) {
        return "Введите корректное целое число";
    }

    if ($number < $min || $number > $max) {
        return "Введите значение от $min до $max";
    }
}

/**
 * Проверка загружаемого файла с изображением.
 * @param array $img - при загрузке файла с изображением проверяется MIME-тип, размер файла, наличие ошибок загрузки и была ли попытка загрузки.
 *
 * @return string - Если не прошла хотя бы одна из проверок - возвращает сообщение об ошибке, иначе- ничего не возвращает.
 */
function checkLotImg(array $img)
{
    $result = '';

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

    if ($result) {
        return $result;
    };
}

/**
 * Валидация установленной даты окончания лота, проверка на корректный формат 'ГГГГ-ММ-ДД' и допустимый диапазон.
 * @param string $date - дата, которую задает продавец лота.
 * @param string $min - минимально допустимая дата.
 * @oaram string $max - максимально допустимая дата.
 *
 * @return - если дата задана корректно, ничего не возвращается. Если дата задана некорректно - возвращается сообщение об ошибке.
 */
function isCorrectDate($date, $min, $max)
{
    $result = '';
    if (!is_date_valid($date)) {
        $result = 'Формат даты ГГГГ-ММ-ДД';
    } elseif ($date < $min || $date > $max) {
        $result = "Введите значение от $min до $max";
    }

    if ($result) {
        return $result;
    }
}

/**
 * Проверка корректной категории добавляемого лота
 * @param array $outfit_categories - список все допустимых категорий лота.
 * @param array $empty_errors - список всех ошибок для "пустых" полей формы.
 * @param int $category_id - идентификатор категории из базы данных.
 *
 * @return - возвращает сообщение об ошибке, если категории с таким идентификатором не существует, иначе ничего не возвращает.
 */
function checkCategoryExistence(array $outfit_categories, array $empty_errors, $category_id)
{
    $category_id = (isset($category_id) && filter_var($category_id, FILTER_VALIDATE_INT)) ? $category_id : 0;
    if (!in_array($category_id, array_column($outfit_categories, 'id'))) {
        return $result = $empty_errors['category'];
    }
}

/**
 * Генерация нового случайного имени файла.
 * @param string $path - путь к директории, куда планируется добавить файл.
 * @param string $file_name - текущий путь к файлу, нужен для определения его расширения.
 *
 * @return string - возвращает новое имя файла с прежним расширением.
 */
function getRandomFileName(string $path, string $file_name)
{
    $extension = pathinfo($file_name, PATHINFO_EXTENSION);

    do {
        $name = uniqid();
        $file = $path . $name . '.' . $extension;
    } while (file_exists($file));

    return $name . '.' . $extension;
}

/**
 * Валидация поля со значением email.
 * @param string $value - адрес электронной почты, который нужно проверить.
 *
 * @return - Если валидация не пройдена - возвращается сообщение об ошибке, если пройдена - ничего не возвращается.
 */
function isCorrectEmail(string $value)
{
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        return 'Введите корректный email';
    }
}

/**
 * Валидация значения поля для введения пароля при регистрации.
 * Пароль должен содержать только буквы и цифры и количество символов должно попадать в заданный диапазон.
 *
 * @param mixed $password - пароль, вводимый пользователем при регистрации.
 * @param int $min - минимально допустимое количество символов.
 * @param int $max - максимально допустимое количество символов.
 *
 * @return - Если пароль не прошел валидацию, возвращается сообщение об ошибке, иначе ничего не возвращается.
 */
function isCorrectPassword($password, $min, $max)
{
    $result = '';
    $password = trim($password);
    $error_length = isCorrectLength($password, $min, $max);
    if ($error_length) {
        $result = $error_length;
    } else {
        (preg_match("/^[0-9a-zA-Zа-яА-Я]+$/",
                $password) !== 1) ? $result = "Пароль должен содержать только буквы и цифры" : '';

    }

    if ($result) {
        return $result;
    }
}


function validationFormFields($method, $required_fields, $rules, $empty_errors, $ranges)
{
    $errors = [];
    $result = '';
//Проверка на заполнение обязательных полей
    foreach ($required_fields as $value) {
        if (!isset($method[$value]) || empty($method[$value])) {
            $errors[$value] = isset($empty_errors[$value]) ? $empty_errors[$value] : 'Поле не должно быть пустым';
        }
    }

//Применение правил валидации к заполненным полям формы
    foreach ($method as $key => $value) {

        if (!isset($errors[$key])) {
            if (isset($value) && !empty($value) && isset($rules[$key])) {
                $result = $rules[$key]($ranges);
            }

            ($result) ? $errors[$key] = $result : '';
        }
    }
    return $errors;
}

