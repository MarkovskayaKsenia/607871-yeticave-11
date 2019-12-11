<main class="container">
<section class="promo">
    <h2 class="promo__title">Нужен стафф для катки?</h2>
    <p class="promo__text">На нашем интернет-аукционе ты найдёшь самое эксклюзивное сноубордическое и горнолыжное снаряжение.</p>
    <ul class="promo__list">
        <?php foreach ($outfit_categories as $value): ?>
            <li class="promo__item promo__item--<?= $value['name']; ?>">
                <a class="promo__link" href="all-lots-cat.php?category=<?= $value['id']; ?>"><?= checkUserData($value['description']); ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<section class="lots">
    <div class="lots__header">
        <h2>Открытые лоты</h2>
    </div>
    <ul class="lots__list">
       <?= $ads_block; ?>
    </ul>
</section>
</main>
