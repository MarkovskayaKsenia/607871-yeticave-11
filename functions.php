<?php
/**
 * Форматирует полученное число на десятичные классы.
 * @param int $num - целое число, которое нужно разделить на классы.
 * @param bool $ruble_sign - признак добавления денежной единицы "рубль": если равен true - добавляется, если false - нет.
 *
 * @return string - отформатированное  на классы число со знаком "рубль" или без, в зависимости от значения $ruble_sign.
 */
function formatPrice(int $num, bool $ruble_sign): string
{
    $price = ceil($num);
    $newFormat = number_format($price, 0, '.', ' ');
    $ruble_sign = ($ruble_sign === true) ? '&#x20bd' : '';
    $price = ($price < 1000) ? $price : $newFormat;
    return $price . ' ' . $ruble_sign;
}

/**
 * Проверка и очистка данных, введенных пользователем
 * @param $str - текстовые данные, введенные пользователем.
 *
 * @return string - возвращает данные, введенные пользователем, очищенные от тегов, лишних пробелов по краям и с экранированными специальными символами.
 */
function checkUserData(string $str): string
{
    return htmlspecialchars(strip_tags(trim($str)));
}

/**
 * Расчет времени, оставшегося до окончания торгов по лоту.
 * @param string $date - дата, представленная в формате 'Y-m-d H:i:s'.
 *
 * @return array - возвращает массив из двух элементов: в первом элементе количество часов, во втором - количество минут.
 */
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

/**
 * Проверка существования данных с определенным ключем в ассоциативном массиве.
 * @param array $arr - ассоциативный массив с данными.
 * @param string $name - название ключа, данные которого проверяются на существование.
 *
 * @return string - если данные с таким ключом в массиве существуют, возвращаются данные. Если данных нет, возвращается пустая строка.
 */
function getFormData(array $arr, string $name): string
{
    return $arr[$name] ?? '';
}

/**
 * Проверка значения на требуемое количество символов.
  * @param $str - значение, которое нужно проверить.
 * @param int $min - минимальное количество символов.
 * @param int $max - максимальное количество символов.
 *
 * @return - Возвращает текст ошибки, если проверки не пройдена, иначе ничего не возвращает.
 */
function isCorrectLength($str, int $min, int $max)
{
    $str = checkUserData($str);
    if (mb_strlen($str) < $min || mb_strlen($str) > $max) {
        return "Значение поля должно быть не меньше $min и не больше $max символов";
    }
}

/**
 * Проверка на то, что значение является целым числом, и входит в разрешенный диапазон.
 * @param $num - значение, которое нужно проверить.
 * @param int $min - минимально допустимое значение числа.
 * @param int $max - максимально допустимое значение числа.
 *
 * @return - Возвращает текст ошибки, если проверки не пройдена, иначе ничего не возвращает.
 */
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

/**
 * Проверка загружаемого файла с изображением.
 * @param array $img - при загрузке файла с изображением проверяется MIME-тип, размер файла, наличие ошибок загрузки и была ли попытка загрузки.
 *
 * @return string - Если не прошла хотя бы одна из проверок - возвращает сообщение об ошибке, иначе- ничего не возвращает.
 */
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

/**
 * Валидация установленной даты окончания лота, проверка на корректный формат 'ГГГГ-ММ-ДД' и допустимый диапазон.
 * @param $date - дата, которую задает продавец лота.
 * @param $min - минимально допустимая дата.
 * @oaram $max - максимально допустимая дата.
 *
 * @return - если дата задана корректно, ничего не возвращается. Если дата задана некорректно - возвращается сообщение об ошибке.
 */
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

/**
 * Проверка корректной категории добавляемого лота
 * @param array $outfit_categories - список все допустимых категорий лота.
 * @param array $empty_errors - список всех ошибок для "пустых" полей формы.
 * @param $category_id - идентификатор категории из базы данных.
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
 * @param string $filename - текущий путь к файлу, нужен для определения его расширения.
 *
 * @return string - возвращает новое имя файла с прежним расширением.
 */
function getRandomFileName(string $path, string $filename)
{
    $extension = pathinfo($filename, PATHINFO_EXTENSION);

    do {
        $name = uniqid();
        $file = $path . $name . '.' . $extension;
    } while (file_exists($file));

    return $name . '.' . $extension;
}

/**
 * Валидация поля со значением email.
 * @param string $str - адрес электронной почты, который нужно проверить.
 *
 * @return - Если валидация не пройдена - возвращается сообщение об ошибке, если пройдена - ничего не возвращается.
 */
function isCorrectEmail(string $str) {
    if(!filter_var($str, FILTER_VALIDATE_EMAIL)) {
        return 'Введите корректный email';
    }
}

/**
 * Валидация значения поля для введения пароля при регистрации.
 * Пароль должен содержать только буквы и цифры и количество символов должно попадать в заданный диапазон.
 *
 * @param $pass - пароль, вводимый пользователем при регистрации.
 * @param int $min - минимально допустимое количество символов.
 * @param int $max - максимально допустимое количество символов.
 *
 * @return - Если пароль не прошел валидацию, возвращается сообщение об ошибке, иначе ничего не возвращается.
 */
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

/**
 * Числовое склонение единиц измерения.
 * @param int $num - число, для еденицы измерения которого нужно выполнить склонение.
 * @param array $nouns - массив с вариантами склонения единицы измерения: [для числа 1, для чисел 2-4, для остальных чисел].
 *
 * @return string - возвращается число и соответствующее склонение единицы измерения.
 */
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

/**
 * Подбор формата времени для отображения "срока давности" сделанной ставки.
 *
 * Если ставка сделана меньше часа назад - срок давности показывает сколько полных минут прошло с момента ставки..
 * Если ставка сделана больше часа назад, но меньше суток - срок давности показывает сколько часов полных часов прошло с момента ставки.
 * Если ставка сделана больше суток назад - срок давности показывает дату и время, когда была сделана ставка.
 *
 * @param string $date - дата и время, когда была сделана ставка. в формате 'Y-m-d H:i:s'.
 *
 * @return string - возвращает срок давности сделанной ставки.
 */
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

/**Проверка на право сделать ставку - доступ к кнопке  "Сделать ставку" на форме.
 * Ставки принимаются только от зарегестрированных юзеров - у юзера должна быть открытая сессия.
 * @param $lot_data - срок окончания торгов по лоту. Если срок прошел, торги по лоту запрещены.
 * @param $bids_list - список ставок по лоту. Юзер не может делать 2 и более ставки подряд на один лот.
 */
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
        'date' =>  ($lot_data['expiry_date'] > date('Y-m-d H:i:s')),
        'session'=> (isset($_SESSION['user']) && $lot_data['user_id'] != $_SESSION['user']['id']),
        'last_bid' => (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] != $potential_winner),
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

/**
 * Определение статуса ставки юзера на странице "Мои ставки".
 * @param $expiry_time - срок окончания торгов по лоту. Должен быть в прошлом.
 * @param $winner_id - в поле "победитель" в таблице лотов указан id юзера.
 * @param $user_id - текущий залогиненный юзер.
 * @param $current_bid - сумма отдельной ставки.
 * @param $max_bid - максимальная существующая ставка по лоту.
 * @return array - Возвращает список классов по каждой ставке для страницы "Мои ставки" и соответствующие подписи к ставкам.
 */
function checkBargainStatus($expiry_time, $winner_id, $user_id, $current_bid, $max_bid)
{
    if ($expiry_time[0] === '00' && $expiry_time[1] === '00') {
        ($winner_id == $user_id && $current_bid == $max_bid) ?
            $result = [
            ' rates__item--win',
            ' timer--win',
            'Ставка выиграла'
        ] : $result = [' rates__item--end', ' timer--end', 'Торги окончены'];
    } elseif ($expiry_time[0] === '') {
        $result = ['', ' timer--finishing', $expiry_time[0] . ':' . $expiry_time[1]];
    } else {
        $result = ['', '', $expiry_time[0] . ':' . $expiry_time[1]];
    }
    return $result;
}







