<?php if($pages_count > 1): ?>
    <ul class="pagination-list">
        <li class="pagination-item pagination-item-prev"><a  href="<?= $url . '&page=' . (($current_page - 1 >  0) ? $current_page - 1 : $current_page); ?>">Назад</a></li>
        <?php foreach ($pages as $value): ?>
            <li class="pagination-item<?= ($value == $current_page) ? ' pagination-item-active' : ''; ?>"><a href="<?= $url . '&page=' . $value;?>"><?= $value; ?></a></li>
        <?php endforeach; ?>
        <li class="pagination-item pagination-item-next"><a href="<?= $url . '&page=' . (($current_page + 1 <= $pages_count) ? $current_page + 1 : $current_page); ?>">Вперед</a></li>
    </ul>
<?php endif; ?>
