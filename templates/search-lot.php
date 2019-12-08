<main>
    <?= $outfit_navigation;?>
    <div class="container">
        <section class="lots">
            <h2>Результаты поиска по запросу «<span><?= checkUserData(getFormData($_GET, 'search')); ?></span>»</h2>
            <ul class="lots__list">
                <?= $adverts_block; ?>
            </ul>
        </section>
        <?= $pagination ?>
    </div>
</main>
