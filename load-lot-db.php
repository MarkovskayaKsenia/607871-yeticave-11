<?php
require_once ('functions.php');

//Путь сохранения изображений.
$newImgPath = __DIR__ . '/uploads/';
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
$outfit_category = $_POST['category'];

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
