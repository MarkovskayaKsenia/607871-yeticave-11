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
        <h2>404 Страница не найдена</h2>
        <p>Данной страницы не существует на сайте.</p>
    </section>
</main>
