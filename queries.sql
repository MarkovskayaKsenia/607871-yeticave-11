USE yeticave;

-- 1.Добавление информации в таблицу категорий;
INSERT INTO outfit_categories (`name`, description)
VALUES ('boards','Доски и лыжи'),
       ('attachment', 'Крепления'),
       ('boots', 'Ботинки'),
       ('clothing', 'Одежда'),
       ('tools', 'Инструменты'),
       ('other', 'Разное');

-- Добавление информации в таблицу пользователей;
INSERT INTO users (email, login, password, contacts)
VALUES ('ElBarto@mail.ru', 'king of the hill', 'EatMyShorts', 'ElBarto@mail.ru, 8-923-515-12-34'),
       ('CrazyDriver@yandex.ru', 'lonely meatball', 'DrunkTest', 'CrazyDriver@yandex.ru, 8-923-120-56-78'),
       ('Gena1990@yandex.ru', 'CrocGena', 'Cheburashka123', '8-903-419-58-31');

-- Добавление существующего списка объявлений;
INSERT INTO users_lots (reg_date, outfit_title, description, img_url, starting_price, expiry_date, bid_step, user_id, winner_id, outfit_category_id)
VALUES ('2019-10-30 12:30:15', '2014 Rossignol District Snowboard','Самая крутая доска на свете, с горы летишь как ветер.','img/lot-1.jpg', 10999, '2019-11-02', 100, 1, 2, 1),
       ('2019-11-02 20:28:36', 'DC Ply Mens 2016/2017 Snowboard','Доска Всевластия, достойная самого боженьки. +100500 к скорости, цена соответствует.','img/lot-2.jpg', 159999, '2019-11-05', 150, 3, 1, 1),
       ('2019-11-06 10:44:18', 'Крепления Union Contact Pro 2015 года размер L/XL','Самые крутые крепы! Влетел на доске в дерево: доска в щепки, крепам - хоть бы хны. Но теперь, без доски, для меня в них нет смысла :(','img/lot-3.jpg', 8000, '2019-12-05', 50, 3, NULL, 2),
       ('2019-11-06 15:10:56', 'Ботинки для сноуборда DC Mutiny Charocal','Эти ботинки служили трем поколениям нашей семьи, от сердца отрываю.','img/lot-4.jpg', 10999, '2019-12-15', 70, 2, NULL, 3),
       ('2019-11-06 09:18:25', 'Куртка для сноуборда DC Mutiny Charocal','Куртка хорошая, почти не ношеная. Покупал для девушки, но она поправилась не влезает, отдаю за полцены.','img/lot-5.jpg', 7500, '2019-12-10', 150, 2, NULL, 4),
       ('2019-11-06 14:01:08', 'Маска Oakley Canopy','Маска, как маска, че ее пробовать... Царапин нет, двойные линзы с покрытием Anti-fog, регулируемый ремешок.','img/lot-6.jpg', 5400, '2019-12-18', 70, 2, NULL, 6);

-- Добавьте ставок для объявлений;
INSERT INTO lots_bids (reg_date, bid_amount, user_id, lot_id)
VALUES ('2019-11-06 17:34:17', 11099, 3, 4),
       ('2019-11-06 16:34:17', 8050, 1, 3),
       ('2019-11-06 16:55:00', 8200, 3, 3);

-- Запрос на получение всех записей из таблицы категорий;
SELECT * FROM outfit_categories;

-- Запрос на самые новые, открытые лоты. Каждый лот должен включать название, стартовую цену, ссылку на изображение, текущую цену, название категории;
SELECT ul.outfit_title,
       ul.starting_price,
       ul.img_url,
       MAX(lb.bid_amount) AS max_bid_amount,
       oc.description
FROM users_lots AS ul
         LEFT JOIN outfit_categories AS oc
                   ON ul.outfit_category_id = oc.id
         LEFT JOIN lots_bids AS lb
                   ON ul.id = lb.lot_id
WHERE ul.expiry_date > NOW()
GROUP BY ul.id
ORDER BY ul.reg_date DESC;

--  Запрос на лот по его id. Получите также название категории, к которой принадлежит лот;
SELECT ul.*,
       oc.description
FROM users_lots AS ul,
     outfit_categories AS oc
WHERE ul.id = 2
AND ul.outfit_category_id = oc.id;

-- Обновление названия лота по его идентификатору
UPDATE users_lots
SET outfit_title = 'Крепления Rossignol JUSTICE 2017 года L/XL'
WHERE id = 3;

-- Получение списка ставок для лота по его идентификатору с сортировкой по дате;
SELECT *
FROM lots_bids
WHERE lots_bids.lot_id = 3
ORDER BY reg_date DESC;
