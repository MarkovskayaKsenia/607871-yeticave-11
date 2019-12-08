<main>
    <?= $outfit_navigation; ?>
    <section class="rates container">
        <h2>Мои ставки</h2>
        <table class="rates__list">
            <?php foreach($sale_adverts as $key => $value): ?>
            <tr class="rates__item<?= $bargain_status[$key][0]; ?>">
                <td class="rates__info">
                    <div class="rates__img">
                        <img src="../<?= $value['img_url']; ?>" width="54" height="40" alt="<?= $value['outfit_title']; ?>">
                    </div>
                    <?php if ($bargain_status[$key][0] === ' rates__item--win') : ?>
                    <div>
                        <h3 class="rates__title"><a href="lot.php?id=<?= $value['id']; ?>"><?= $value['outfit_title']; ?></a></h3>
                        <p><?= $value['contacts'];?></p>
                    </div>
                    <?php else: ?>
                        <h3 class="rates__title"><a href="lot.php?id=<?= $value['id']; ?>"><?= $value['outfit_title']; ?></a></h3>
                    <?php endif; ?>
                </td>
                <td class="rates__category">
                    <?= checkUserData($value['outfit_category']); ?>
                </td>
                <td class="rates__timer">
                    <div class="timer<?= $bargain_status[$key][1]; ?>"><?= $bargain_status[$key][2]; ?></div>
                </td>
                <td class="rates__price">
                    <?= formatPrice($value['bid'], true); ?>
                </td>
                <td class="rates__time">
                    <?= formatTimeDistance($value['bid_date']); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>
</main>
