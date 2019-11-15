<?php
require_once ('functions.php');
?>
<main class="container">
<section class="promo">
    <h2 class="promo__title">Нужен стафф для катки?</h2>
    <p class="promo__text">На нашем интернет-аукционе ты найдёшь самое эксклюзивное сноубордическое и горнолыжное снаряжение.</p>
    <ul class="promo__list">
        <?php foreach ($outfit_categories as $value): ?>
            <li class="promo__item promo__item--<?= $value['name']; ?>">
                <a class="promo__link" href="pages/all-lots.html"><?= checkUserData($value['description']); ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<section class="lots">
    <div class="lots__header">
        <h2>Открытые лоты</h2>
    </div>
    <ul class="lots__list">
        <?php foreach ($sale_ads as $key => $value): ?>
            <li class="lots__item lot">
                <div class="lot__image">
                    <img src="<?= $value['img_url']; ?>" width="350" height="260" alt="<?= $value['outfit_title']; ?>">
                </div>
                <div class="lot__info">
                    <span class="lot__category"><?= checkUserData($value['outfit_category']); ?></span>
                    <h3 class="lot__title"><a class="text-link" href="lot.php?id=<?= $value['id']; ?>"><?= $value['outfit_title']; ?></a></h3>
                    <div class="lot__state">
                        <div class="lot__rate">
                            <span class="lot__amount"><?= ($value['bid_count'] == 0) ? 'Стартовая цена' : 'Текущая цена' ; ?></span>
                            <span class="lot__cost"><?= formatPrice($value['price']); ?></span>
                        </div>
                        <div class="lot__timer timer<?= ($expiry_time[$key][0] === '00') ? ' timer--finishing' : ''; ?>">
                            <?= $expiry_time[$key][0] . ':' . $expiry_time[$key][1]; ?>
                        </div>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
</main>
