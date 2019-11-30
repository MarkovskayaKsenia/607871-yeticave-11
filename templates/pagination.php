<?php if($pages_count > 1): ?>
    <ul class="pagination-list">
        <li class="pagination-item pagination-item-prev"><a  href="<?= $url . '&page=' . (($cur_page - 1 > 0) ? $cur_page - 1 : $cur_page); ?>">Назад</a></li>
        <?php foreach ($pages as $value): ?>
            <li class="pagination-item<?= ($value == $cur_page) ? ' pagination-item-active' : ''; ?>"><a href="<?= $url . '&page=' . $value;?>"><?=$value;?></a></li>
        <?php endforeach; ?>
        <li class="pagination-item pagination-item-next"><a href="<?= $url . '&page=' . (($cur_page + 1 <= $pages_count) ? $cur_page + 1 : $cur_page); ?>">Вперед</a></li>
    </ul>
<?php endif; ?>
