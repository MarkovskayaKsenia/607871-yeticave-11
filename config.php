<?php
session_start();
//Установка временной зоны
date_default_timezone_set('Europe/Moscow');

//Настройки подключения к базе данных
$mysql = mysqli_connect('localhost', 'ksenia', 'thesimpsons', 'yeticave');
mysqli_set_charset($mysql, 'utf8');

if (!$mysql) {
    print ('Ошибка подключения: ' . mysqli_connect_error());
    die();
}
