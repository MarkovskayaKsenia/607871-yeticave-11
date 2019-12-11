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
    $new_format = number_format($price, 0, '.', ' ');
    $ruble_sign = ($ruble_sign === true) ? '&#x20bd' : '';
    $price = ($price < 1000) ? $price : $new_format;
    return $price . ' ' . $ruble_sign;
}

/**
 * Проверка и очистка данных, введенных пользователем
 * @param string $value - текстовые данные, введенные пользователем.
 *
 * @return string - возвращает данные, введенные пользователем, очищенные от тегов, лишних пробелов по краям и с экранированными специальными символами.
 */
function checkUserData(string $value): string
{
    return filter_var(trim($value), FILTER_SANITIZE_SPECIAL_CHARS);
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

    if ($expiry_date <= $now) {
        return ['00', '00'];
    }

    $time_difference = (array)date_diff($now, $expiry_date);
    $hours_in_day = 24;
    $hours_left = $time_difference['d'] * $hours_in_day + $time_difference['h'];
    $hours_left = ($hours_left >= 10) ? ('' . $hours_left) : ('0' . $hours_left);
    $minutes_left = ($time_difference['i'] >= 10) ? ('' . $time_difference['i']) : ('0' . $time_difference['i']);
    $time_left = [$hours_left, $minutes_left];
    return $time_left;
}

/**
 * Проверка существования данных с определенным ключем в ассоциативном массиве.
 * @param array $array - ассоциативный массив с данными.
 * @param string $name - название ключа, данные которого проверяются на существование.
 *
 * @return string - если данные с таким ключом в массиве существуют, возвращаются данные. Если данных нет, возвращается пустая строка.
 */
function getFormData(array $array, string $name): string
{
    return $array[$name] ?? '';
}

/**
 * Числовое склонение единиц измерения.
 * @param int $number - число, для еденицы измерения которого нужно выполнить склонение.
 * @param array $nouns - массив с вариантами склонения единицы измерения: [для числа 1, для чисел 2-4, для остальных чисел].
 *
 * @return string - возвращается число и соответствующее склонение единицы измерения.
 */
function declensionOfNouns(int $number, array $nouns): string {
    $noun = $nouns[2] ?? ''; // Значение по умолчанию.

    $divisionRemainder = $number % 10;
    if ($divisionRemainder === 1) {
        $noun = $nouns[0] ?? '';
    }
    if (in_array($divisionRemainder, [2, 3, 4])) {
        $noun = $nouns[1] ?? '';
    }

    if ($number >= 11 && $number <= 14) {
        $noun = $nouns[2] ?? '';
    }

    return "$number $noun";
}

/**
 * Подбор формата времени для отображения "срока давности" сделанной ставки.
 * @param string $date - дата и время, когда была сделана ставка. в формате 'Y-m-d H:i:s'.
 *
 * @return string - возвращает срок давности сделанной ставки.
 */
function formatTimeDistance(string $date): string {

    $reg_date = strtotime($date);
    $now = strtotime(date('Y-m-d H:i:s'));
    $time_difference = $now - $reg_date;

    $time_declensions = [
        'hours' => ['час', 'часа', 'часов'],
        'minutes' => ['минуту', 'минуты', 'минут']
    ];

    $time_distance = [
        'hours' => floor($time_difference / 3600),
        'minutes' => floor($time_difference / 60)
    ];

    if($time_distance['hours'] > 24) {
        return date('y.m.d в H:i', $reg_date);
    }

    $key = ($time_distance['hours'] > 0) ? 'hours' : 'minutes';
    $measure_display = $time_distance[$key];
    $date_format = $time_declensions[$key];

    return declensionOfNouns($measure_display, $date_format) . ' назад';
}

/**Проверка на право сделать ставку - доступ к кнопке  "Сделать ставку" на форме.
 * Ставки принимаются только от зарегестрированных юзеров - у юзера должна быть открытая сессия.
 * @param array $lot_data - массив, содержащий срок окончания торгов и id продавца.
 * @param $bids_list - список ставок по лоту.
 *
 * @return boolean - Если true - есть право на ставку, false - права на ставку нет.
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
    foreach($rules as $value) {
        if ($value !== true) {
            return false;
        }
    }
    return true;
}

/**
 * Определение статуса ставки юзера на странице "Мои ставки".
 * @param array $expiry_time - массив из двух элементов, первый элемент - количество часов до окончания торгов,
 * второй элемент - количество минут.
 * @param int $winner_id - в поле "победитель" в таблице лотов указан id юзера.
 * @param int $user_id - текущий залогиненный юзер.
 * @param int $current_bid - сумма отдельной ставки.
 * @param int $max_bid - максимальная существующая ставка по лоту.
 * @return array - Возвращает список классов по каждой ставке для страницы "Мои ставки" и соответствующие подписи к ставкам.
 */
function checkBargainStatus($expiry_time, $winner_id, $user_id, $current_bid, $max_bid)
{
    $bid_status = [
        'win' => [' rates__item--win', ' timer--win', 'Ставка выиграла'],
        'end' => [' rates__item--end', ' timer--end', 'Торги окончены'],
        'finishing' => ['', ' timer--finishing', "$expiry_time[0]:$expiry_time[1]"],
        'normal' => ['', '', "$expiry_time[0]:$expiry_time[1]"],
    ];

    $rules = [
        'win' => ($winner_id == $user_id && $current_bid == $max_bid),
        'end' => ($expiry_time[0] === '00' && $expiry_time[1] === '00'),
        'finishing' => ($expiry_time[0] === '00'),
        'normal' => true,
    ];

    foreach ($rules as $key => $value) {
        if ($value === true) {
            return $bid_status[$key];
        }
    }
}
/**
 * Определение параметров пагинации.
 * @param int $current_page - текущая страница,
 * @param array $parameters - параметры для формирования url страниц.
 * @param int $page_items - количество элементов на странице.
 * @param int $rows_count - общее количество элементов.
 * @param string $script_name - путь к файлу, на который выводится пагинация.
 * @return array - Возвращает список данных, необходимых для пагинации.
 */

function getPaginationOptions($current_page, $parameters, $page_items, $rows_count, $script_name)
{
    $pagination['current_page'] = (intval(filter_var($current_page, FILTER_VALIDATE_INT))) ?? 1;
    $pagination['pages_count'] = ceil($rows_count / $page_items);
    $pagination['offset_ads'] = ($current_page - 1) * $page_items;
    $pagination['pages'] = range(1, $pagination['pages_count']);
    $build_query = http_build_query($parameters);
    $pagination['url'] = '/' . $script_name . '?' . $build_query;

    return $pagination;
}


