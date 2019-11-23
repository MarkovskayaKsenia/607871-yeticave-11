<?php
$mysql = mysqli_connect('localhost', 'ksenia', 'thesimpsons', 'yeticave');
mysqli_set_charset($mysql, 'utf8');

if (!$mysql) {
    print ('Ошибка подключения: ' . mysqli_connect_error());
    die();
}
