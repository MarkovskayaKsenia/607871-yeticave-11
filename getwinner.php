<?php
require_once('vendor/autoload.php');
require_once('helpers.php');
require_once('functions.php');
require_once('config.php');


$transport = new Swift_SmtpTransport ('phpdemo.ru', 25);
$transport->setUsername('keks@phpdemo.ru');
$transport->setPassword('htmlacademy');

$mailer = new Swift_Mailer($transport);

$sql_winner = "SELECT ul.id AS lot_id, outfit_title, bid_amount, lb.user_id AS user_id, u.login AS login, u.email  AS email, "
    . "(SELECT MAX(bid_amount) FROM lots_bids WHERE lb.lot_id = lots_bids.lot_id group by lots_bids.lot_id ) AS max_bid "
    . "FROM users_lots as ul "
    . "INNER JOIN lots_bids AS lb ON ul.id = lb.lot_id "
    . "LEFT JOIN users as u ON lb.user_id = u.id "
    . "WHERE expiry_date < NOW() AND winner_id IS NULL "
    . "HAVING lb.bid_amount = max_bid";

$query_winner = mysqli_query($mysql, $sql_winner);
$message = new Swift_Message();
$message->setSubject('Поздравляем с победой на аукционе');
$message->setFrom(['keks@phpdemo.ru' => 'YetiCave']);

if ($query_winner && mysqli_num_rows($query_winner) > 0) {
    $result_winner = mysqli_fetch_all($query_winner, MYSQLI_ASSOC);
    $count = 0;

    foreach ($result_winner as $value) {
        $message_content = include_template('email.php', ['winner' => $value]);
        $message->setBody($message_content, 'text/html');
        $message->setTo([$value['email'] => $value['login']]);
        $result = $mailer->send($message);
        $count = ($result) ? $count + 1 : $count;
    }

    $output = "Отправлено:" . declensionOfNouns($count,
            ['письмо', 'письма', 'писем']) . " победителям из " . count($result_winner);
    print($output);
}



