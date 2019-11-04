<?php
require_once ('helpers.php');
require_once ('data.php');
require_once('functions.php');

$title = 'Главная';

//Массив объявлений о продаже
$sale_ads = [
    [
        'outfit_title' => '2014 Rossignol District Snowboard',
        'outfit_category' => 'Доски и лыжи',
        'price' => 10999,
        'url' => 'img/lot-1.jpg',
        'expiry_date' =>'2019-11-02',
    ],
    [
        'outfit_title' => 'DC Ply Mens 2016/2017 Snowboard',
        'outfit_category' => 'Доски и лыжи',
        'price' => 159999,
        'url' => 'img/lot-2.jpg',
        'expiry_date' =>'2019-11-09',
    ],
    [
        'outfit_title' => 'Крепления Union Contact Pro 2015 года размер L/XL',
        'outfit_category' => 'Крепления',
        'price' => 8000,
        'url' => 'img/lot-3.jpg',
        'expiry_date' =>'2019-11-08',
    ],
    [
        'outfit_title' => 'Ботинки для сноуборда DC Mutiny Charocal',
        'outfit_category' => 'Ботинки',
        'price' => 10999,
        'url' => 'img/lot-4.jpg',
        'expiry_date' =>'2019-11-10',
    ],
    [
        'outfit_title' => 'Куртка для сноуборда DC Mutiny Charocal',
        'outfit_category' => 'Одежда',
        'price' => 7500,
        'url' => 'img/lot-5.jpg',
        'expiry_date' =>'2019-11-5',
    ],
    [
        'outfit_title' => 'Маска Oakley Canopy',
        'outfit_category' => 'Разное',
        'price' => 5400,
        'url' => 'img/lot-6.jpg',
        'expiry_date' =>'2019-11-04',
    ],
];
//Расчет срока окончания торгов для всех объявлений
$expiry_time = array_map('countExpiryTime', (array_column($sale_ads, 'expiry_date')));

//Заполнение шаблонов данными и вставка на старницу
$page_content = include_template('main.php', [
    'outfit_categories' => $outfit_categories,
    'sale_ads' => $sale_ads,
    'expiry_time'=> $expiry_time,
    ]);

$layout_content = include_template('layout.php', [
    'content' => $page_content,
    'outfit_categories' => $outfit_categories,
    'user_name' => $user_name,
    'is_auth' => $is_auth,
    'title' => $title,
    ]);

print($layout_content);
