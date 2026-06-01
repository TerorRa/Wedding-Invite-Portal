<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$code = trim((string)($_GET['code'] ?? ''));
$guest = null;

if ($code !== '') {
    $guest = R::findOne('guests', 'invite_code = ?', [$code]);
}

if ($guest !== null) {
    logInviteAction((int)$guest->id, 'viewed_ticket');
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$path = strtok($_SERVER['REQUEST_URI'] ?? '/ticket.php', '?') ?: '/ticket.php';
$ticketUrl = $scheme . '://' . $host . $path . '?code=' . urlencode($code);
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($ticketUrl);
$invitePath = str_replace('/ticket.php', '/invite.php', $path);
$inviteUrl = $scheme . '://' . $host . $invitePath . '?code=' . urlencode($code) . '&edit=1';
$aboutPath = str_replace('/ticket.php', '/about.php', $path);
$aboutUrl = $scheme . '://' . $host . $aboutPath . '?code=' . urlencode($code);
$telegramConfig = is_file(__DIR__ . '/config/telegram.php') ? require __DIR__ . '/config/telegram.php' : [];
$telegramBotUsername = ltrim((string)($telegramConfig['bot_username'] ?? 'Hive_KPP_System_bot'), '@');
$telegramVideoUrl = (string)($telegramConfig['video_group_url'] ?? $telegramConfig['group_url'] ?? '');

if ($telegramVideoUrl === '') {
    $telegramVideoUrl = 'https://t.me/' . rawurlencode($telegramBotUsername);
}
$calendarUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
    . '&text=' . rawurlencode('Весілля — Ростислав & Катерина')
    . '&dates=20260801T130000/20260802T000000'
    . '&location=' . rawurlencode('Петрівський Бровар, Київська область')
    . '&details=' . rawurlencode('Весілля Ростислава та Катерини');
$photoFiles = [];
$photoDirectory = __DIR__ . '/assets/foto';
$styleVersion = (string)(@filemtime(__DIR__ . '/assets/css/style.css') ?: time());

if (is_dir($photoDirectory)) {
    $files = glob($photoDirectory . '/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE) ?: [];
    sort($files, SORT_NATURAL);

    foreach ($files as $file) {
        if (is_file($file)) {
            $photoFiles[] = 'assets/foto/' . basename($file);
        }
    }
}
$ticketNumber = $guest !== null ? (string)$guest->ticket_number : '';
$photoGroups = [[], []];

if ($photoFiles !== []) {
    $splitIndex = (int)ceil(count($photoFiles) / 2);
    $photoGroups[0] = array_slice($photoFiles, 0, $splitIndex);
    $photoGroups[1] = array_slice($photoFiles, $splitIndex);
}
?>
<!doctype html>
<html lang="uk" class="no-js">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wedding Pass</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= e($styleVersion) ?>">
</head>

<body>
    <main class="page-shell pass-shell">
        <div class="cosmic-effects cosmic-effects--pass" aria-hidden="true"></div>
        <?php if ($guest === null): ?>
            <section class="welcome reveal">
                <p class="eyebrow">Wedding Pass</p>
                <h1>Квиток не знайдено</h1>
                <p>Перевірте посилання або зверніться до організаторів.</p>
            </section>
        <?php elseif ($guest->status === 'declined' || (int)$guest->will_attend === 0): ?>
            <section class="welcome reveal pass-declined-card">
                <p class="eyebrow">Дякуємо за відповідь</p>
                <h1><?= e($guest->name) ?>,</h1>
                <p>Нам шкода, що ви не зможете бути з нами.</p>
                <p>Ми дуже хотіли б, щоб ви були присутні поруч у цей день. Якщо матимете бажання, запишіть коротке відеопривітання для нас і надішліть його в Telegram-групу.</p>
                <a class="section-action pass-about-button" href="<?= e($telegramVideoUrl) ?>" target="_blank" rel="noreferrer">Надіслати відеопривітання</a>
                <a class="section-action btn-o" href="<?= e($inviteUrl) ?>">Повернутися до запрошення</a>
            </section>
        <?php else: ?>
            <div class="pass-ticket-layout">
                <section>
                </section>
                <?php /* if ($photoGroups[0] !== []): ?>
                    <section class="pass-side-filmstrip pass-side-filmstrip--left reveal" aria-label="Фотоспогади ліворуч">
                        <div class="pass-side-filmstrip__track">
                            <?php for ($loop = 0; $loop < 2; $loop++): ?>
                                <?php foreach ($photoGroups[0] as $index => $photo): ?>
                                    <figure class="pass-side-filmstrip__frame">
                                        <img src="<?= e($photo) ?>" alt="Фото Ростислава та Катерини <?= $index + 1 ?>" loading="eager" decoding="async" <?= $loop === 0 && $index < 2 ? ' fetchpriority="high"' : '' ?>>
                                    </figure>
                                <?php endforeach; ?>
                            <?php endfor; ?>
                        </div>
                    </section>
                <?php endif; */ ?>

                <section class="wedding-pass pass-card reveal" data-confetti-on-load>
                    <span class="pass-glow pass-glow--one" aria-hidden="true"></span>
                    <span class="pass-glow pass-glow--two" aria-hidden="true"></span>
                    <img class="pass-symbol pass-symbol--planet" src="assets/img/bck/little_prince_transparent_planet.png" alt="" aria-hidden="true">
                    <img class="pass-symbol pass-symbol--plane" src="assets/img/bck/airplane.png" alt="" aria-hidden="true">
                    <div class="pass-main">
                        <p class="eyebrow">Wedding Pass</p>
                        <h1><?= e($guest->name) ?> <?php if ((int)$guest->plus_one === 1 && !empty($guest->plus_one_name)): ?>
                                та <?= e($guest->plus_one_name) ?>
                            <?php endif; ?>
                        </h1>
                        <p class="pass-note">Дякуємо, що ви готові поринути разом із нами у цю зоряну подорож любові, музики й теплих спогадів.</p>

                        <dl class="pass-details">
                            <div>
                                <dt>Дата</dt>
                                <dd>01.08.2026</dd>
                            </div>
                            <div>
                                <dt>Місце</dt>
                                <dd>Петрівський Бровар, Київська область</dd>
                            </div>
                            <?php if (!empty($guest->table_number)): ?>
                                <div>
                                    <dt>Стіл</dt>
                                    <dd><?= e($guest->table_number) ?></dd>
                                </div>
                            <?php endif; ?>
                            <div>
                                <dt>Статус</dt>
                                <dd>Зустрінемось на Весіллі</dd>
                            </div>
                        </dl>
                    </div>

                    <!--<div class="pass-qr">
                        <img src="<?= e($qrUrl) ?>" alt="QR-код Wedding Pass">
                        <p>Покажіть цей QR-код при вході.</p>
                    </div>-->
                    <div class="pass-qr">

                            <span class="ticket__stub-label">Покажіть цей код на весіллі.</span>
                            <span class="ticket__barcode" aria-hidden="true"></span>
                            <span class="ticket__barcode-num"><?= e($ticketNumber) ?></span>
  
                        </div>
                            

                    <div class="pass-actions">
                        <a class="section-action pass-about-button" href="<?= e($aboutUrl) ?>">Про нас</a>
                        <a class="section-action" href="<?= e($inviteUrl) ?>">В запрошення</a>
                        <a class="section-action btn-o" href="<?= e($calendarUrl) ?>" target="_blank" rel="noreferrer">Додати до календаря</a>

                    </div>
                </section>

                <?php /*if ($photoGroups[1] !== []): ?>
                    <section class="pass-side-filmstrip pass-side-filmstrip--right reveal" aria-label="Фотоспогади праворуч">
                        <div class="pass-side-filmstrip__track">
                            <?php for ($loop = 0; $loop < 2; $loop++): ?>
                                <?php foreach ($photoGroups[1] as $index => $photo): ?>
                                    <figure class="pass-side-filmstrip__frame">
                                        <img src="<?= e($photo) ?>" alt="Фото Ростислава та Катерини <?= $splitIndex + $index + 1 ?>" loading="eager" decoding="async" <?= $loop === 0 && $index < 2 ? ' fetchpriority="high"' : '' ?>>
                                    </figure>
                                <?php endforeach; ?>
                            <?php endfor; ?>
                        </div>
                    </section>
                <?php endif; */ ?>
            </div>
        <?php endif; ?>
    </main>
    <script src="assets/js/invite.js"></script>
</body>

</html>
