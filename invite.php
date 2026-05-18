<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$code = trim((string)($_GET['code'] ?? ''));
$guest = null;

if ($code !== '') {
    $guest = R::findOne('guests', 'invite_code = ?', [$code]);
}

if ($guest !== null) {
    $changed = false;

    if ($guest->status === 'invited') {
        $guest->status = 'opened';
        $changed = true;
    }

    if (empty($guest->opened_at)) {
        $guest->opened_at = date('Y-m-d H:i:s');
        $changed = true;
    }

    if ($changed) {
        R::store($guest);
    }

    logInviteAction((int)$guest->id, 'opened_invite');
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="uk" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Запрошення на весілля</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main class="invite-page">
        <?php if ($guest === null): ?>
            <section class="not-found reveal">
                <p class="eyebrow">Запрошення</p>
                <h1>Запрошення не знайдено</h1>
                <p>Перевірте посилання або зверніться до організаторів.</p>
            </section>
        <?php else: ?>
            <section class="invite-hero reveal">
                <div class="hero-date">1 серпня 2026</div>
                <p class="eyebrow">Персональне запрошення</p>
                <h1><?= e($guest->name) ?>, будемо раді бачити вас на нашому весіллі</h1>
                <p class="couple-names">Ростислав & Катерина</p>
                <a class="hero-action" href="#invitation">Відкрити запрошення</a>
            </section>

            <section class="invite-section invitation-card reveal" id="invitation">
                <p class="eyebrow">Запрошення</p>
                <h2>Ми хочемо розділити цей день з людьми, які для нас важливі.</h2>
                <p>
                    Запрошуємо вас на наше весілля, щоб разом прожити мить церемонії,
                    теплу вечерю, перший танець і вечір, який залишиться у памʼяті.
                </p>
                <dl class="event-details">
                    <div>
                        <dt>Дата</dt>
                        <dd>1 серпня 2026</dd>
                    </div>
                    <div>
                        <dt>Місце</dt>
                        <dd>Petrovskyi Brovar</dd>
                    </div>
                </dl>
            </section>

            <section class="invite-section reveal">
                <h2>Таймлайн події</h2>
                <div class="timeline">
                    <article>
                        <span>15:00</span>
                        <h3>Збір гостей</h3>
                        <p>Зустрічаємось у Petrovskyi Brovar.</p>
                    </article>
                    <article>
                        <span>16:00</span>
                        <h3>Церемонія</h3>
                        <p>Найважливіші слова цього дня.</p>
                    </article>
                    <article>
                        <span>16:40</span>
                        <h3>Welcome</h3>
                        <p>Легкі напої, фото та перші привітання.</p>
                    </article>
                    <article>
                        <span>17:30</span>
                        <h3>Вечеря</h3>
                        <p>Тости, музика, танці та теплі розмови.</p>
                    </article>
                    <article>
                        <span>19:00</span>
                        <h3>Перший танець</h3>
                        <p>Момент, з якого починається вечірня магія.</p>
                    </article>
                    <article>
                        <span>20:00</span>
                        <h3>Вечірня частина</h3>
                        <p>Святкуємо, танцюємо і насолоджуємось компанією.</p>
                    </article>
                </div>
            </section>

            <section class="invite-section dress-code reveal">
                <h2>Дрес-код</h2>
                <p>Будемо вдячні за елегантні святкові образи у спокійних природних відтінках.</p>
                <div class="swatches" aria-label="Кольори дрес-коду">
                    <span style="background:#efe5d8"></span>
                    <span style="background:#c8b8a4"></span>
                    <span style="background:#8f9a8b"></span>
                    <span style="background:#4f5a51"></span>
                </div>
            </section>

            <section class="invite-section rsvp-section reveal" id="rsvp">
                <h2>Підтвердіть участь</h2>
                <form class="rsvp-form" action="submit_rsvp.php" method="post">
                    <input type="hidden" name="invite_code" value="<?= e($guest->invite_code) ?>">

                    <fieldset>
                        <legend>Чи будете ви присутні?</legend>
                        <label>
                            <input type="radio" name="will_attend" value="1" required>
                            Так, буду
                        </label>
                        <label>
                            <input type="radio" name="will_attend" value="0" required>
                            На жаль, не зможу
                        </label>
                    </fieldset>

                    <fieldset>
                        <legend>Гості</legend>
                        <label>
                            <input type="radio" name="plus_one" value="0" checked>
                            Буду сам
                        </label>
                        <?php if ((int)$guest->max_plus_one === 1): ?>
                            <label>
                                <input type="radio" name="plus_one" value="1">
                                Буду з +1
                            </label>
                            <label class="plus-one-name is-hidden">
                                Ім'я супутника
                                <input type="text" name="plus_one_name" autocomplete="name">
                            </label>
                        <?php endif; ?>
                    </fieldset>

                    <label>
                        Що будете пити?
                        <select name="drink">
                            <option value="">Оберіть варіант</option>
                            <option value="Вино">Вино</option>
                            <option value="Шампанське">Шампанське</option>
                            <option value="Віскі">Віскі</option>
                            <option value="Безалкогольне">Безалкогольне</option>
                            <option value="Інше">Інше</option>
                        </select>
                    </label>

                    <label>
                        Обмеження по їжі
                        <textarea name="food_notes" rows="3"></textarea>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="need_transfer" value="1">
                        Потрібен трансфер
                    </label>

                    <label>
                        Пісня, яку хочете почути
                        <input type="text" name="song_request">
                    </label>

                    <label>
                        Побажання
                        <textarea name="wish" rows="4"></textarea>
                    </label>

                    <button type="submit">Надіслати відповідь</button>
                </form>
            </section>

            <section class="invite-section final-note reveal">
                <p class="eyebrow">До зустрічі</p>
                <h2>Дякуємо, що ви є частиною нашої історії.</h2>
                <p>Вашу відповідь ми отримаємо одразу, а після підтвердження для вас відкриється персональний Wedding Pass.</p>
            </section>
        <?php endif; ?>
    </main>
    <script src="assets/js/invite.js"></script>
</body>
</html>
