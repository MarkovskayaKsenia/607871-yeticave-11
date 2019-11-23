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
    <form class="form form--add-lot container <?= (count($errors) != 0) ? ' form--invalid' : ''; ?>" action="add.php" method="post">
        <h2>Добавление лота</h2>
        <div class="form__container-two">
            <div class="form__item<?= getFormData($errors, 'lot-name') ? ' form__item--invalid' : ''; ?>" >
                <label for="lot-name">Наименование <sup>*</sup></label>
                <input id="lot-name" type="text" name="lot-name" placeholder="Введите наименование лота" value="<?= checkUserData(getFormData($_POST, 'lot-name')); ?>" >
                <span class="form__error"><?= getFormData($errors, 'lot-name'); ?></span>
            </div>
            <div class="form__item<?= getFormData($errors, 'category') ? ' form__item--invalid' : ''; ?>">
                <label for="category">Категория <sup>*</sup></label>
                <select id="category" name="category">
                    <option>Выберите категорию</option>
                    <?php foreach ($outfit_categories as $value): ?>
                    <option <?= (getFormData($_POST, 'category') == $value['description']) ? 'selected="true"' : '' ; ?>><?= checkUserData($value['description']); ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="form__error"><?= getFormData($errors, 'category'); ?></span>
            </div>
        </div>
        <div class="form__item form__item--wide<?= getFormData($errors, 'message') ? ' form__item--invalid' : ''; ?>">
            <label for="message">Описание <sup>*</sup></label>
            <textarea id="message" name="message" placeholder="Напишите описание лота"><?= checkUserData(getFormData($_POST, 'message')); ?></textarea>
            <span class="form__error"><?= getFormData($errors, 'message'); ?></span>
        </div>
        <div class="form__item form__item--file">
            <label>Изображение <sup>*</sup></label>
            <div class="form__input-file">
                <input class="visually-hidden" type="file" id="lot-img" value="">
                <label for="lot-img">
                    Добавить
                </label>
            </div>
        </div>
        <div class="form__container-three">
            <div class="form__item form__item--small<?= getFormData($errors, 'lot-rate') ? ' form__item--invalid' : ''; ?>">
                <label for="lot-rate">Начальная цена <sup>*</sup></label>
                <input id="lot-rate" type="text" name="lot-rate" placeholder="0" reqiured value="<?= checkUserData(getFormData($_POST, 'lot-rate')); ?>">
                <span class="form__error"><?= getFormData($errors, 'lot-rate'); ?></span>
            </div>
            <div class="form__item form__item--small<?= getFormData($errors, 'lot-step') ? ' form__item--invalid' : ''; ?>">
                <label for="lot-step">Шаг ставки <sup>*</sup></label>
                <input id="lot-step" type="text" name="lot-step" placeholder="0" value="<?= checkUserData(getFormData($_POST, 'lot-step')); ?>">
                <span class="form__error"><?= getFormData($errors, 'lot-step'); ?></span>
            </div>
            <div class="form__item<?= getFormData($errors, 'lot-date') ? ' form__item--invalid' : ''; ?>">
                <label for="lot-date">Дата окончания торгов <sup>*</sup></label>
                <input class="form__input-date" id="lot-date" type="text" name="lot-date" placeholder="Введите дату в формате ГГГГ-ММ-ДД" value="<?= checkUserData(getFormData($_POST, 'lot-date')); ?>">
                <span class="form__error"><?= getFormData($errors, 'lot-date'); ?></span>
            </div>
        </div>
        <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
        <button type="submit" class="button">Добавить лот</button>
    </form>
</main>
