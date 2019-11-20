    <main>
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
                        <div class="lot-item__timer timer<?= ($expiry_time[0] === '00') ? ' timer--finishing' : ''; ?>">
                            <?= $expiry_time[0] . ':' . $expiry_time[1]; ?>
                        </div>
                        <div class="lot-item__cost-state">
                            <div class="lot-item__rate">
                                <span class="lot-item__amount"><?= ($lot_data['bids_count'] == 0) ? 'Стартовая цена': 'Текущая цена'; ?></span>
                                <span class="lot-item__cost"><?= $lot_data['price']; ?></span>
                            </div>
                            <div class="lot-item__min-cost">
                                Мин. ставка <span><?= formatPrice($lot_data['price'] + $lot_data['bid_step']); ?></span>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </section>
    </main>
