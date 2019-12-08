<main>
    <?= $outfit_navigation; ?>
    <div class="container">
        <section class="lots">
            <h2>Все лоты в категории <span>«<?= $category_description; ?>»</span></h2>
            <ul class="lots__list">
                <?= $adverts_block; ?>
            </ul>
        </section>
        <?= $pagination ?>
    </div>
</main>
