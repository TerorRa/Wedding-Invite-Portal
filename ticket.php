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

function safeGreetingCode(string $code): string
{
    return preg_replace('/[^a-zA-Z0-9_-]/', '_', $code) ?: 'guest';
}

function findVideoGreeting(string $directory, string $safeCode): ?string
{
    $matches = glob($directory . '/' . $safeCode . '.*') ?: [];
    sort($matches, SORT_NATURAL);

    foreach ($matches as $match) {
        if (is_file($match)) {
            return $match;
        }
    }

    return null;
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
$telegramGalleryUrl = 'https://t.me/+uS_pxorqzKxkZmVi';

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
$videoGreetingDirectory = __DIR__ . '/assets/video_greetings';
$styleVersion = (string)(@filemtime(__DIR__ . '/assets/css/style.css') ?: time());
$scriptVersion = (string)(@filemtime(__DIR__ . '/assets/js/invite.js') ?: time());
$videoGreetingMessage = '';
$videoGreetingError = '';
$safeInviteCode = safeGreetingCode($code);

if ($guest !== null && ($guest->status === 'declined' || (int)$guest->will_attend === 0) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $videoAction = (string)($_POST['video_action'] ?? '');

    if (!is_dir($videoGreetingDirectory)) {
        mkdir($videoGreetingDirectory, 0775, true);
    }

    if ($videoAction === 'delete_video') {
        $existingVideo = findVideoGreeting($videoGreetingDirectory, $safeInviteCode);

        if ($existingVideo !== null && unlink($existingVideo)) {
            $videoGreetingMessage = 'Відеопривітання видалено. Ви можете записати або завантажити нове.';
        } else {
            $videoGreetingError = 'Не вдалося знайти відеопривітання для видалення.';
        }
    }

    if ($videoAction === 'upload_video') {
        $uploadedFile = $_FILES['video_greeting'] ?? null;
        $allowedExtensions = ['mp4', 'mov', 'webm', 'm4v'];

        if (!is_array($uploadedFile) || (int)($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $videoGreetingError = 'Оберіть або запишіть відео перед надсиланням.';
        } elseif ((int)$uploadedFile['error'] !== UPLOAD_ERR_OK) {
            $videoGreetingError = 'Не вдалося завантажити відео. Спробуйте ще раз.';
        } else {
            $originalName = (string)($uploadedFile['name'] ?? '');
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if (!in_array($extension, $allowedExtensions, true)) {
                $videoGreetingError = 'Підтримуються відео у форматах MP4, MOV, WEBM або M4V.';
            } else {
                $existingVideo = findVideoGreeting($videoGreetingDirectory, $safeInviteCode);

                if ($existingVideo !== null) {
                    unlink($existingVideo);
                }

                $targetPath = $videoGreetingDirectory . '/' . $safeInviteCode . '.' . $extension;

                if (move_uploaded_file((string)$uploadedFile['tmp_name'], $targetPath)) {
                    $videoGreetingMessage = 'Дякуємо, відеопривітання збережено.';
                } else {
                    $videoGreetingError = 'Не вдалося зберегти відео. Спробуйте ще раз.';
                }
            }
        }
    }
}


$ticketNumber = $guest !== null ? (string)$guest->ticket_number : '';
$partnerName = $guest !== null ? trim((string)$guest->plus_one_name) : '';
$invitedGuestNames = $guest !== null ? [(string)$guest->name] : [];

if ($partnerName !== '') {
    $invitedGuestNames[] = $partnerName;
}

$primaryAttends = $guest !== null && $guest->primary_attends !== null ? (int)$guest->primary_attends : 1;
$partnerAttends = $guest !== null && $guest->partner_attends !== null ? (int)$guest->partner_attends : (int)$guest->plus_one;
$ticketGuestNames = [];

if ($guest !== null && $primaryAttends === 1) {
    $ticketGuestNames[] = (string)$guest->name;
}

if ($partnerName !== '' && $partnerAttends === 1) {
    $ticketGuestNames[] = $partnerName;
}

if ($guest !== null && $ticketGuestNames === []) {
    $ticketGuestNames[] = (string)$guest->name;
}

$videoGreetingPath = $guest !== null ? findVideoGreeting($videoGreetingDirectory, $safeInviteCode) : null;
$videoGreetingRelative = $videoGreetingPath !== null ? 'assets/video_greetings/' . basename($videoGreetingPath) : null;
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
                <h1><?= e(implode(' та ', $invitedGuestNames)) ?></h1>
                <p>Ми дуже хотіли б, щоб ви були присутні поруч у цей день. Якщо матимете бажання, запишіть коротке відеопривітання для нас або надішліть готове відео з галереї.</p>
                <div class="pass-video-greeting">
                    <h2>Відеопривітання</h2>
                    <?php if ($videoGreetingMessage !== ''): ?>
                        <p class="pass-form-status"><?= e($videoGreetingMessage) ?></p>
                    <?php endif; ?>
                    <?php if ($videoGreetingError !== ''): ?>
                        <p class="pass-form-status pass-form-status--error"><?= e($videoGreetingError) ?></p>
                    <?php endif; ?>
                    <?php if ($videoGreetingRelative !== null): ?>
                        <video class="pass-video-preview" controls preload="metadata" src="<?= e($videoGreetingRelative) ?>"></video>
                    <?php endif; ?>
                    <form class="pass-video-form" action="<?= e($ticketUrl) ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="video_action" value="upload_video">
                        <div class="pass-file-picker">
                            <span class="pass-file-picker__title">Записати або обрати відео</span>
                            <input class="pass-video-file-input" type="file" name="video_greeting" accept="video/*,.mp4,.mov,.webm,.m4v" data-file-input required>
                            <button class="pass-file-picker__control" type="button" data-video-pick="gallery">Обрати з галереї</button>
                            <button class="pass-file-picker__control pass-file-picker__control--secondary" type="button" data-video-pick="camera">Записати відео</button>
                            <span class="pass-file-picker__name" data-file-name aria-live="polite">Файл не обрано</span>
                        </div>
                        <button class="section-action pass-about-button pass-video-submit" type="submit" data-upload-submit>
                            <span data-submit-text><?= $videoGreetingRelative !== null ? 'Замінити відеопривітання' : 'Надіслати відеопривітання' ?></span>
                            <span class="pass-upload-spinner" aria-hidden="true"></span>
                        </button>
                    </form>
                    <?php if ($videoGreetingRelative !== null): ?>
                        <form action="<?= e($ticketUrl) ?>" method="post">
                            <input type="hidden" name="video_action" value="delete_video">
                            <button class="section-action btn-o" type="submit">Видалити відеопривітання</button>
                        </form>
                    <?php endif; ?>
                </div>
                <a class="section-action pass-edit-response-button" href="<?= e($inviteUrl) ?>">Змінити відповідь</a>
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

                <section class="wedding-pass pass-card reveal" data-confetti-on-load2>
                    <!--  <span class="pass-glow pass-glow--one" aria-hidden="true"></span>
                    <span class="pass-glow pass-glow--two" aria-hidden="true"></span>
                    <span class="pass-cross-star pass-cross-star--one" aria-hidden="true"></span>
                    <span class="pass-cross-star pass-cross-star--two" aria-hidden="true"></span>
                    <span class="pass-cross-star pass-cross-star--three" aria-hidden="true"></span>
                    <span class="pass-cross-star pass-cross-star--four" aria-hidden="true"></span>
                    <span class="pass-cross-star pass-cross-star--five" aria-hidden="true"></span>
                    <img class="pass-symbol pass-symbol--planet" src="assets/img/bck/little_prince_transparent_planet.png" alt="" aria-hidden="true">
                    <img class="pass-symbol pass-symbol--plane" src="assets/img/bck/airplane.png" alt="" aria-hidden="true">-->
                    <div class="pass-main">
                        <h1><?= e(implode(' & ', $ticketGuestNames)) ?></h1>
                        <p class="pass-note">Дякуємо, що ви готові поринути разом із нами у цю зоряну подорож любові, музики й теплих спогадів.</p>
                        <dl class="pass-details">
                            <div class="pass-countdown">
                                <p>Наша зірка вже майже готова засяяти на небосхилі</p>
                                <div>
                                    <dt>Місце</dt>
                                    <dd>Петрівський Бровар, Київська область</dd>
                                </div> 
                                <div>
                                    <dt>Дата</dt>
                                    <dd>13:00 01.08.2026</dd>
                                </div>
                                <p>Залишилось чекати лише</p>
                                <div class="pass-countdown__timer" data-countdown="2026-08-01T14:00:00">
                                    <div><strong data-days>00</strong><span>днів</span></div>
                                    <div><strong data-hours>00</strong><span>годин</span></div>
                                    <div><strong data-minutes>00</strong><span>хвилин</span></div>
                                    <div><strong data-seconds>00</strong><span>секунд</span></div>
                                </div>                            
                            </div>

                           
                            <!-- <?php if (!empty($guest->table_number)): ?>
                                <div>
                                    <dt>Стіл</dt>
                                    <dd><?= e($guest->table_number) ?></dd>
                                </div>
                            <?php endif; ?>
                            <div>
                                <dt>Статус</dt>
                                <dd>Зустрінемось на Весіллі</dd>
                            </div>-->
                        </dl>
                    </div>

                    <!--<div class="pass-qr">
                        <img src="<?= e($qrUrl) ?>" alt="QR-код Wedding Pass">
                        <p>Покажіть цей QR-код при вході.</p>
                    </div>-->
                    <!-- <div class="pass-qr">

                        <span class="ticket__stub-label">Покажіть цей код на весіллі.</span>
                        <span class="ticket__barcode" aria-hidden="true"></span>
                        <span class="ticket__barcode-num"><?= e($ticketNumber) ?></span>

                    </div>-->


                    <div class="pass-actions">
                        <a class="section-action pass-about-button" href="<?= e($aboutUrl) ?>">Трохи нас</a>
                        <a class="section-action pass-edit-response-button" href="<?= e($inviteUrl) ?>">Змінити відповідь</a>
                        <a class="section-action btn-o" href="<?= e($calendarUrl) ?>" target="_blank" rel="noreferrer">Додати до календаря</a>

                    </div>
                    <div class="pass-share-block">
                        <p>Будемо раді, якщо ви додатково зможете зробити фото/відео записи весілля і поділитесь з нами в окремій групі Telegram.</p>
                        <a class="section-action btn-o" href="<?= e($telegramGalleryUrl) ?>" target="_blank" rel="noreferrer">Група в Telegram</a>
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
    <script src="assets/js/invite.js?v=<?= e($scriptVersion) ?>"></script>
</body>

</html>
