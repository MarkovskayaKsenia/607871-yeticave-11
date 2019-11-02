<?php
require_once ('helpers.php');
require_once ('data.php');

$title = 'Главная';

$sale_ads = [
    [
        'img_title' => '2014 Rossignol District Snowboard',
        'outfit_category' => 'Доски и лыжи',
        'price' => 10999,
        'url' => 'img/lot-1.jpg',
        'expire' =>'2019-11-02',
    ],
    [
        'img_title' => 'DC Ply Mens 2016/2017 Snowboard',
        'outfit_category' => 'Доски и лыжи',
        'price' => 159999,
        'url' => 'img/lot-2.jpg',
        'expire' =>'2019-11-09',
    ],
    [
        'img_title' => 'Крепления Union Contact Pro 2015 года размер L/XL',
        'outfit_category' => 'Крепления',
        'price' => 8000,
        'url' => 'img/lot-3.jpg',
        'expire' =>'2019-11-08',
    ],
    [
        'img_title' => 'Ботинки для сноуборда DC Mutiny Charocal',
        'outfit_category' => 'Ботинки',
        'price' => 10999,
        'url' => 'img/lot-4.jpg',
        'expire' =>'2019-11-10',
    ],
    [
        'img_title' => 'Куртка для сноуборда DC Mutiny Charocal',
        'outfit_category' => 'Одежда',
        'price' => 7500,
        'url' => 'img/lot-5.jpg',
        'expire' =>'2019-11-5',
    ],
    [
        'img_title' => 'Маска Oakley Canopy',
        'outfit_category' => 'Разное',
        'price' => 5400,
        'url' => 'img/lot-6.jpg',
        'expire' =>'2019-11-04',
    ],
];

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
