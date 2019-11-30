    <main>
        <?= $outfit_nav; ?>
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
                        <div class="lot-item__timer timer<?= ($expiry_time[0] === '00') ? ' timer--finishing' : ''; ?>">
                            <?= $expiry_time[0] . ':' . $expiry_time[1]; ?>
                        </div>
                        <div class="lot-item__cost-state">
                            <div class="lot-item__rate">
                                <span class="lot-item__amount"><?= ($lot_data['bids_count'] == 0) ? 'Стартовая цена': 'Текущая цена'; ?></span>
                                <span class="lot-item__cost"><?= formatPrice($lot_data['price'], false); ?></span>
                            </div>
                            <div class="lot-item__min-cost">
                                Мин. ставка <span><?= formatPrice($lot_data['price'] + $lot_data['bid_step'], true); ?></span>
                            </div>
                        </div>
                        <?php if(bidResolution($lot_data, $bids_list)): ?>
                        <form class="lot-item__form" action="" method="post" autocomplete="off">
                            <p class="lot-item__form-item form__item<?= isset($errors['cost']) ? ' form__item--invalid' : ''; ?>">
                                <label for="cost">Ваша ставка</label>
                                <input id="cost" type="text" name="cost" placeholder="<?= formatPrice($lot_data['price'] + $lot_data['bid_step'], false); ?>" value="<?= checkUserData(getFormData($_POST, 'cost')); ?>">
                                <span class="form__error"><?= $errors['cost'] ; ?></span>
                            </p>
                            <button type="submit" class="button">Сделать ставку</button>
                        </form>
                        <?php endif; ?>
                    </div>
                        <div class="history">
                            <h3>История ставок (<span><?= $bids_count; ?></span>)</h3>
                            <table class="history__list">
                                <?php foreach($bids_list as $value): ?>
                                    <tr class="history__item">
                                        <td class="history__name"><?= $value['login']; ?></td>
                                        <td class="history__price"><?= formatPrice($value['bid_amount'], true); ?></td>
                                        <td class="history__time"><?= formatTimeDistance($value['reg_date']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                </div>
            </div>
        </section>
    </main>
