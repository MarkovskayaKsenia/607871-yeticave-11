<?php
require_once('vendor/autoload.php');
require_once('helpers.php');
require_once('functions.php');
require_once('config.php');


$transport = new Swift_SmtpTransport ('smtp.mailtrap.io', 2525);
$transport->setUsername('12b6986765ab99');
$transport->setPassword('eb3de6062df982');


$mailer = new Swift_Mailer($transport);

$sql_find_winner = "SELECT ul.id AS lot_id, outfit_title, bid_amount, lb.user_id AS user_id, u.login AS login, u.email  AS email, "
    . "(SELECT MAX(bid_amount) FROM lots_bids WHERE lb.lot_id = lots_bids.lot_id group by lots_bids.lot_id ) AS max_bid "
    . "FROM users_lots as ul "
    . "INNER JOIN lots_bids AS lb ON ul.id = lb.lot_id "
    . "LEFT JOIN users as u ON lb.user_id = u.id "
    . "WHERE expiry_date < NOW() AND winner_id IS NULL "
    . "HAVING lb.bid_amount = max_bid";

$query_find_winner = mysqli_query($mysql, $sql_find_winner);
$message = new Swift_Message();
$message->setSubject('Поздравляем с победой на аукционе');
$message->setFrom(['keks@phpdemo.ru' => 'YetiCave']);
$count = 0;
if ($query_find_winner && mysqli_num_rows($query_find_winner) > 0) {
    $result_find_winner = mysqli_fetch_all($query_find_winner, MYSQLI_ASSOC);

    foreach ($result_find_winner as $value) {
        $sql_set_winner = "UPDATE users_lots SET winner_id = " . $value['user_id']
            . " WHERE users_lots.id = " . $value['lot_id'];
        $result_set_winner = mysqli_query($mysql, $sql_set_winner);

        if ($result_set_winner) {
            $message_content = include_template('email.php', ['winner' => $value]);
            $message->setBody($message_content, 'text/html');
            $message->setTo([$value['email'] => $value['login']]);
            $result_send_mail = $mailer->send($message);
            $count = ($result_send_mail) ? $count + 1 : $count;
            sleep(5);
        }
    }
    $output = "Отправлено:" . declensionOfNouns($count,
            ['письмо', 'письма', 'писем']) . " о победе в аукционе из " . count($result_find_winner);
    print($output);
}




