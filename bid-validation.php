<?php
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //Массив полей, обязательных к заполнению
    $required_fields = ['cost',];
    //Текст ошибок для пустых полей формы
    $empty_errors = [
        'cost' => 'Укажите вашу ставку',
    ];

    //Массив допустимых диапазонов для полей формы
    $ranges = [
        'cost_min' => $lot_data['price'] + $lot_data['bid_step'],
        'cost_max' => $lot_data['price'] + $lot_data['bid_step'] + 15000,
    ];

    //Правила валидации для полей
    $rules = [
        'cost' => function (array $ranges) {
            return isCorrectNumber($_POST['cost'], $ranges['cost_min'], $ranges['cost_max']);
        },
    ];
    //Проверка на заполнение обязательных полей
    foreach ($required_fields as $value) {
        if (!isset($_POST[$value]) || empty($_POST[$value])) {
            $errors[$value] = isset($empty_errors[$value]) ? $empty_errors[$value] : 'Поле не должно быть пустым';
        }
    }

    //Применение правил валидации к заполненным полям формы
    foreach ($_POST as $key => $value) {
        if (!isset($errors[$key])) {
            if (isset($value) && !empty($value) && isset($rules[$key])) {
                $result = $rules[$key]($ranges);
            }

            (isset($result) && !empty($result)) ? $errors[$key] = $result : '';
        }
    }

    //Добавляем ставку на лот в базу данных
    if (count($errors) === 0) {
        $sql_bid = "INSERT INTO lots_bids (reg_date, bid_amount, user_id, lot_id) VALUES (?, ?, ?, ?)";
        //Подготовка параметров для передачи в запрос
        $reg_date = date('Y-m-d H:i:s');
        $bid_amount = checkUserData($_POST['cost']);
        $user_id = $_SESSION['user']['id'];

        $stm_bid = db_get_prepare_stmt($mysql, $sql_bid, [$reg_date, $bid_amount, $user_id, $lot_data['id']]);
        $res_bid = mysqli_stmt_execute($stm_bid);

        if (!$res_bid) {
           print('Что-то пошло не так и ваша ставка не добавилась.');
           exit();
         } else {
            header('Location: lot.php?id=' . $lot_data['id']);
        }
    }

}



