<?php
require_once('helpers.php');
require_once('functions/general.php');

//Путь сохранения изображений.
$new_img_path = __DIR__ . '/uploads/';
$new_img_name = getRandomFileName($new_img_path, $_FILES['lot-img']['name']);
$new_img_src = $new_img_path . $new_img_name;

//Загрузка файла из временной папки
move_uploaded_file($_FILES['lot-img']['tmp_name'], $new_img_src);

//Запрос на добавление лота в базу данных
$sql_add = "INSERT INTO users_lots (reg_date, outfit_title, description, img_url, starting_price, expiry_date, "
    . "bid_step, user_id, outfit_category_id) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";

//Подготовка данных для передачи в базу данных
$reg_date = date('Y-m-d H:i:s');
$outfit_title = checkUserData($_POST['lot-name']);
$description = checkUserData($_POST['message']);
$img_url = 'uploads/' . $new_img_name;
$starting_price = checkUserData($_POST['lot-rate']);
$expiry_date = checkUserData($_POST['lot-date']);
$bid_step = checkUserData($_POST['lot-step']);
$user_id = $_SESSION['user']['id'];
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

if(mysqli_stmt_execute($stm_add)) {
    $last_lot_id = mysqli_insert_id($mysql);
    header('Location: /lot.php?id=' . $last_lot_id);
    exit();
}

