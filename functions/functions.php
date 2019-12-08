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

    if ($expiry_date > $now) {
        $time_difference = (array)date_diff($now, $expiry_date);
        $hours_in_day = 24;
        $hours_left = $time_difference['d'] * $hours_in_day + $time_difference['h'];
        $hours_left = ($hours_left >= 10) ? ('' . $hours_left) : ('0' . $hours_left);
        $minutes_left = ($time_difference['i'] >= 10) ? ('' . $time_difference['i']) : ('0' . $time_difference['i']);
        $time_left = [$hours_left, $minutes_left];
    } else {
        $time_left = ['00', '00'];
    }

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

    if ($number >= 11 and $number <= 14) {
        $i = 2;
    } else {
        switch ($number % 10){
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

    return $number . ' ' . $nouns[$i];
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
    $time_difference = $now - $reg_date;

    $time_declensions = [
        'hours' => ['час', 'часа', 'часов'],
        'minutes' => ['минуту', 'минуты', 'минут']
    ];

    $time_distance = [
        'days' => floor($time_difference / 86400),
        'hours' => floor($time_difference / 3600),
        'minutes' => floor($time_difference / 60)
    ];

    if($time_distance['days'] > 0) {
        $date_format= date('y.m.d',$reg_date) . ' в ' . date('H:i', $reg_date);
    } else{
        $key = ($time_distance['hours'] > 0) ? 'hours' : 'minutes';
        $number_display = $time_distance[$key];
        $date_format = $time_declensions[$key];
    }

    $result = (isset($number_display)) ? declensionOfNouns($number_display, $date_format) . ' назад': $date_format;
    return $result;
}

/**Проверка на право сделать ставку - доступ к кнопке  "Сделать ставку" на форме.
 * Ставки принимаются только от зарегестрированных юзеров - у юзера должна быть открытая сессия.
 * @param array $lot_data - массив, содержащий срок окончания торгов по и id продавца.
 * Если срок прошел, торги по лоту запрещены.
 * @param $bids_list - список ставок по лоту. Юзер не может делать 2 и более ставки подряд на один лот.
 *
 * return boolean - Если true - есть право на ставку, false - права на ставку нет.
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
 * @param array $expiry_times - массив из двух элементов, первый элемент - количество часов до окончания торгов,
 * во втором - количество минут.
 * @param int $winner_id - в поле "победитель" в таблице лотов указан id юзера.
 * @param int $user_id - текущий залогиненный юзер.
 * @param int $current_bid - сумма отдельной ставки.
 * @param int $max_bid - максимальная существующая ставка по лоту.
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

function getPaginationOptions($current_page, $parameters, $page_items, $rows_count, $script_name)
{
    $pagination['current_page'] = (intval(filter_var($current_page, FILTER_VALIDATE_INT))) ?? 1;
    $pagination['pages_count'] = ceil($rows_count / $page_items);
    $pagination['offset_adverts'] = ($current_page - 1) * $page_items;
    $pagination['pages'] = range(1, $pagination['pages_count']);
    $build_query = http_build_query($parameters);
    $pagination['url'] = '/' . $script_name . '?' . $build_query;

    return $pagination;
}


