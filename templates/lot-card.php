        <nav class="nav">
            <ul class="nav__list container">
                <?php foreach ($outfit_categories as $value): ?>
                    <li class="nav__item">
                        <a href="all-lots.html"><?= checkUserData($value['description']); ?></a>
                    </li>
                <?php endforeach; ?>

            </ul>
        </nav>
        <section class="lot-item container">
            <h2><?= $lot_data['outfit_title']; ?></h2>
            <div class="lot-item__content">
                <div class="lot-item__left">
                    <div class="lot-item__image">
                        <img src="../<?= $lot_data['img_url']; ?>" width="730" height="548" alt="<?= $lot_data['outfit_title']; ?>">
                    </div>
                    <p class="lot-item__category">Категория: <span><?= $lot_data['outfit_category']; ?></span></p>
                    <p class="lot-item__description"><?= $lot_data['description']; ?></p>
                </div>
                <div class="lot-item__right">
                    <div class="lot-item__state">
                        <div class="lot-item__timer timer">
                            <?= $expiry_time[0] . ':' . $expiry_time[1]; ?>
                        </div>
                        <div class="lot-item__cost-state">
                            <div class="lot-item__rate">
                                <span class="lot-item__amount"><?= ($lot_data['bid_count'] == 0) ? 'Стартовая цена': 'Текущая цена'; ?></span>
                                <span class="lot-item__cost"><?= $lot_data['price']; ?></span>
                            </div>
                            <div class="lot-item__min-cost">
                                Мин. ставка <span><?= formatPrice($lot_data['price'] + $lot_data['bid_step']); ?></span>
                            </div>
                        </div>
                        <form class="lot-item__form" action="https://echo.htmlacademy.ru" method="post" autocomplete="off">
                            <p class="lot-item__form-item form__item form__item--invalid">
                                <label for="cost">Ваша ставка</label>
                                <input id="cost" type="text" name="cost" placeholder="<?= substr(formatPrice($lot_data['price'] + $lot_data['bid_step']), 0, -2); ?>">
                                <span class="form__error">Введите наименование лота</span>
                            </p>
                            <button type="submit" class="button">Сделать ставку</button>
                        </form>
                    </div>
                    <div class="history">
                        <h3>История ставок (<span>10</span>)</h3>
                        <table class="history__list">
                            <?php foreach($bids_list as $value): ?>
                            <tr class="history__item">
                                <td class="history__name"><?= $value['login']; ?></td>
                                <td class="history__price"><?= formatPrice($value['bid_amount']); ?></td>
                                <td class="history__time">5 минут назад</td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            </div>
        </section>

