<?php
require_once ('helpers.php');
require_once ('data.php');

$title = 'Главная';

$page_content = include_template('main.php', [
    'outfit_categories' => $outfit_categories,
    'sale_ads' => $sale_ads,
    ]);

$layout_content = include_template('layout.php', [
    'content' => $page_content,
    'outfit_categories' => $outfit_categories,
    'user_name' => $user_name,
    'is_auth' => $is_auth,
    'title' => $title,
    ]);

print($layout_content);
