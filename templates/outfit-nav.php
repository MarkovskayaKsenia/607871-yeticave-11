<nav class="nav">
    <ul class="nav__list container">
        <?php foreach ($outfit_categories as $value): ?>
            <li class="nav__item">
                <a href="all-lots-cat.php?category=<?= $value['id']; ?>"><?= checkUserData($value['description']); ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
