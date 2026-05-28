<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$rsvpError = $_SESSION['rsvp_error'] ?? null;
unset($_SESSION['rsvp_error']);

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

    if (empty($guest->ticket_number)) {
        $ticketNumber = generateTicketNumber((string)$guest->invite_code);

        while (R::count('guests', 'ticket_number = ?', [$ticketNumber]) > 0) {
            $ticketNumber = generateTicketNumber((string)$guest->invite_code . random_int(1000, 9999));
        }

        $guest->ticket_number = $ticketNumber;
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

$styleVersion = (string)(@filemtime(__DIR__ . '/assets/css/style.css') ?: time());
$inviteScriptVersion = (string)(@filemtime(__DIR__ . '/assets/js/invite.js') ?: time());

$ticketNumber = $guest !== null ? (string)$guest->ticket_number : '';
$personalGreeting = $guest !== null ? trim((string)$guest->personal_greeting) : '';
$invitationType = $guest !== null ? (string)($guest->invitation_type ?: ((int)$guest->max_plus_one === 1 ? 'single_plus_one' : 'single')) : 'single';
$isCoupleInvite = $invitationType === 'couple';
$partnerName = $guest !== null ? trim((string)$guest->plus_one_name) : '';
$inviteDisplayName = $guest !== null
    ? (string)$guest->name . ($isCoupleInvite && $partnerName !== '' ? ' та ' . $partnerName : '')
    : '';
$hasOptionalPlusOne = $guest !== null && !$isCoupleInvite && (int)$guest->max_plus_one === 1;
$mainDrinkLabel = $guest !== null ? 'Який напій обирає ' . (string)$guest->name . '?' : 'Ваш бажаний напій?';
$rsvpDeadline = new DateTimeImmutable('2026-07-02 23:59:59', new DateTimeZone('Europe/Kiev'));
$isPastRsvpDeadline = new DateTimeImmutable('now', new DateTimeZone('Europe/Kiev')) > $rsvpDeadline;
$hasGuestAnswered = $guest !== null && (
    trim((string)$guest->answered_at) !== ''
    || in_array((string)$guest->status, ['confirmed', 'declined'], true)
    || trim((string)$guest->will_attend) !== ''
);
$hasConfirmedAttendance = $guest !== null && (
    (string)$guest->status === 'confirmed'
    || (string)$guest->will_attend === '1'
);
$allowInviteEdit = (string)($_GET['edit'] ?? '') === '1';
$isInviteTooLate = $guest !== null && $isPastRsvpDeadline && !$hasGuestAnswered;
$shouldShowInviteGate = $guest !== null && !$isInviteTooLate;

if ($hasConfirmedAttendance && !$allowInviteEdit && empty($rsvpError)) {
    header('Location: ticket.php?code=' . urlencode($code));
    exit;
}

if ($isInviteTooLate) {
    header('Location: late_invite.php?code=' . urlencode($code));
    exit;
}

$calendarUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
    . '&text=' . rawurlencode('Весілля — Ростислав & Катерина')
    . '&dates=20260801T130000/20260802T000000'
    . '&location=' . rawurlencode('Петрівський Бровар, Київська область')
    . '&details=' . rawurlencode('Весілля Ростислава та Катерини');
$telegramConfig = is_file(__DIR__ . '/config/telegram.php') ? require __DIR__ . '/config/telegram.php' : [];
$telegramBotUsername = ltrim((string)($telegramConfig['bot_username'] ?? 'Hive_KPP_System_bot'), '@');
$telegramBotUrl = $guest !== null
    ? 'https://t.me/' . rawurlencode($telegramBotUsername) . '?start=' . rawurlencode((string)$guest->invite_code)
    : 'https://t.me/' . rawurlencode($telegramBotUsername);
$programItems = [
    [
        'event_time' => '15:00',
        'title' => 'Збір гостей',
        'description' => 'Зустрічаємось у Петрівському Броварі.',
    ],
    [
        'event_time' => '16:00',
        'title' => 'Церемонія',
        'description' => 'Найважливіші слова цього дня.',
    ],
    [
        'event_time' => '16:40',
        'title' => 'Welcome',
        'description' => 'Легкі напої, фото та перші привітання.',
    ],
    [
        'event_time' => '17:30',
        'title' => 'Вечеря',
        'description' => 'Тости, музика і теплі розмови.',
    ],
    [
        'event_time' => '19:00',
        'title' => 'Перший танець',
        'description' => 'Момент, із якого починається вечірня магія.',
    ],
    [
        'event_time' => '23:00',
        'title' => 'Фінал вечора',
        'description' => 'Обійми, останні фото і тепле до зустрічі.',
    ],
];

try {
    $storedProgramItems = R::getAll(
        'SELECT event_time, title, description FROM dayprograms WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
    );

    if ($storedProgramItems !== []) {
        $programItems = $storedProgramItems;
    }
} catch (Throwable $exception) {
    // Keep the default program available until install.sql is applied.
}

$ticketStartTime = (string)($programItems[0]['event_time'] ?? '15:00');
?>
<!doctype html>
<html lang="uk" class="no-js">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ростислав & Катерина — весілля 01.08.2026</title>
    <meta property="og:title" content="Ростислав & Катерина — весілля 01.08.2026">
    <meta property="og:description" content="Серед мільйонів зірок ми будемо раді бачити вас поруч">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,400&family=Great+Vibes&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= e($styleVersion) ?>">
</head>

<body class="<?= $shouldShowInviteGate ? 'has-invite-gate celestial-theme' : 'celestial-theme' ?>">
    <main>
        <?php if ($guest === null): ?>
            <section class="page-shell">
                <div class="not-found reveal">
                    <p class="eyebrow">Запрошення</p>
                    <h1>Запрошення не знайдено</h1>
                    <p>Перевірте посилання або зверніться до організаторів.</p>
                </div>
            </section>
        <?php else: ?>
            <section class="invite-opening" aria-label="Відкрити запрошення">
                <div class="splash-stars" aria-hidden="true"></div>
                <img class="splash-symbol splash-symbol--planet" src="assets/img/bck/little_prince_transparent_planet.png" alt="" aria-hidden="true">
                <img class="splash-symbol splash-symbol--plane" src="assets/img/bck/airplane.png" alt="" aria-hidden="true">
                <span class="splash-orbit" aria-hidden="true"></span>
                <button type="button" class="ticket-wrap" data-open-invite aria-label="Відкрити запрошення">
                    <span class="ticket">
                        <span class="ticket__notch-shadow ticket__notch-shadow--l"></span>
                        <span class="ticket__notch-shadow ticket__notch-shadow--r"></span>

                        <span class="ticket__top">
                            <span class="ticket__top-stars"></span>
                            <svg class="ticket__moon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" fill="rgba(255,255,255,.95)" stroke="rgba(255,255,255,.5)" stroke-width=".5" />
                            </svg>
                            <span class="ticket__top-pre">Квиток на відвідування</span>
                            <span class="ticket__top-title">Планети кохання</span>
                        </span>

                        <span class="ticket__mid">
                            <span class="ticket__label">ЗАПРОШЕННЯ</span>
                            <span class="ticket__names<?= $isCoupleInvite ? ' ticket__names--couple' : '' ?>"><?= e($inviteDisplayName) ?></span>
                            <span class="ticket__date-row">
                                <span class="ticket__date-day">01</span>
                                <span class="ticket__date-month">серпня 2026</span>
                            </span>
                            <span class="ticket__time"><?= e($ticketStartTime) ?></span>
                            <span class="ticket__venue">Петрівський Бровар</span>
                        </span>

                        <span class="ticket__sep"></span>

                        <span class="ticket__bot">
                            <span class="ticket__stub-label">Ваш квиток</span>
                            <span class="ticket__barcode" aria-hidden="true"></span>
                            <span class="ticket__barcode-num"><?= e($ticketNumber) ?></span>
                        </span>
                    </span>

                    <span class="ticket__hint">
                        <span><i class="ticket__hint-dot"></i> Розгорнути квиток до нашої планети</span>
                    </span>
                </button>
            </section>

            <div class="invite-content" id="inviteContent">
                <div class="cosmic-effects" aria-hidden="true"></div>
                <section class="hero sec reveal" id="hero">
                    <div class="starfield" aria-hidden="true"></div>
                    <div class="hero-moon" aria-hidden="true"></div>
                    <img class="hero-symbol hero-symbol--fox" src="assets/img/bck/little_prince_transparent_fox.png" alt="" aria-hidden="true">
                    <img class="hero-symbol hero-symbol--telescope" src="assets/img/bck/little_prince_transparent_telescope.png" alt="" aria-hidden="true">
                    <div class="hero-content">
                        <p class="hero-pre">Ми одружуємось</p>
                        <h1 class="hero-names">Ростислав <span>&</span> Катерина</h1>
                        <div class="hero-divider"><span></span><i></i><span></span></div>
                        <p class="hero-date">01 <small>серпня</small> 2026</p>

                        <p class="hero__motto"> Серед мільйонів зірок я знайшов тебе </p>

                        <div class="hero__img-wrap">
                            <img class="hero__img" src="assets/img/hero-new.webp" alt="Ростислав та Катерина">
                        </div>
                    </div>
                </section>

                <section class="sec sec--w invitation-section reveal" id="invitation">
                    <div class="ws invitation-wrap">
                        <h2 class="t-h">Дорогі та рідні!</h2>
                        <div class="moon-divider"><span></span><i></i><span></span></div>
                        <?php if ($personalGreeting !== ''): ?>
                            <p class="inv__text personal-greeting"><?= e($personalGreeting) ?></p>
                        <?php else: ?>
                            <p class="inv__text">Ми довго мандрували кожен своєю орбітою,<br>доки не знайшли планету, на якій хочеться залишитись разом.<br>Запрошуємо вас стати частиною нашого маленького всесвіту<br>і розділити з нами день, де народжується наша сімʼя.</p>
                        <?php endif; ?>
                        <br>
                            <img class="inv__image" src="assets/img/rose.png" alt="Троянда під куполом">
                            <p>Те, що справді важливе, ми відчуваємо серцем.</p>
                        </div>

                    </div>
                </section>

                <section class="sec sec--ivory sec--visible countdown-section reveal" id="countdown">
                    <div class="ws countdown-wrap">
                        <p class="t-scr">Зворотний відлік</p>
                        <h2 class="t-h">До миті, коли народиться наше сузірʼя</h2>
                        <p class="t-sub">1 серпня 2026</p>
                        <div class="moon-divider"><span></span><i></i><span></span></div>
                        <div class="cd countdown" data-countdown="2026-08-01T15:00:00">
                            <div class="cd__b"><strong class="cd__n" data-days>00</strong><span class="cd__l">днів</span></div>
                            <div class="cd__b"><strong class="cd__n" data-hours>00</strong><span class="cd__l">годин</span></div>
                            <div class="cd__b"><strong class="cd__n" data-minutes>00</strong><span class="cd__l">хвилин</span></div>
                            <div class="cd__b"><strong class="cd__n" data-seconds>00</strong><span class="cd__l">секунд</span></div>
                        </div>
                        <div class="celestial-hourglass" aria-hidden="true">
                            <svg class="hourglass-orbit" viewBox="0 0 340 260" fill="none">
                                <g opacity=".55" filter="drop-shadow(0 0 8px rgba(255,255,255,.8)) drop-shadow(0 0 16px rgba(255,255,255,.3))">
                                    <ellipse cx="170" cy="130" rx="155" ry="95" stroke="#fff" stroke-width="1.6" transform="rotate(15,170,130)" />
                                    <ellipse cx="170" cy="130" rx="160" ry="42" stroke="#fff" stroke-width="1.2" transform="rotate(-10,170,130)" />
                                    <g fill="#fff" filter="drop-shadow(0 0 10px rgba(255,255,255,1)) drop-shadow(0 0 20px rgba(255,255,255,.5))">
                                        <path d="M200,33 L202,43 200,53 198,43Z" />
                                        <path d="M190,43 L200,40.5 210,43 200,45.5Z" />
                                        <path d="M322,146 L324,156 322,166 320,156Z" />
                                        <path d="M312,156 L322,153.5 332,156 322,158.5Z" />
                                        <path d="M140,219 L142,229 140,239 138,229Z" />
                                        <path d="M130,229 L140,226.5 150,229 140,231.5Z" />
                                        <path d="M18,106 L20,116 18,126 16,116Z" />
                                        <path d="M8,116 L18,113.5 28,116 18,118.5Z" />
                                        <path d="M328,114 L330,124 328,134 326,124Z" />
                                        <path d="M318,124 L328,121.5 338,124 328,126.5Z" />
                                        <path d="M12,138 L14,148 12,158 10,148Z" />
                                        <path d="M2,148 L12,145.5 22,148 12,150.5Z" />
                                    </g>
                                </g>
                            </svg>

                            <!-- celestial hourglass (front layer) -->
                            <svg class="hourglass-svg" width="180" height="340" viewBox="0 0 180 280" fill="none">
                                <defs>
                                    <linearGradient id="glassTop" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#b8cce4" stop-opacity=".5" />
                                        <stop offset="100%" stop-color="#c6d5e6" stop-opacity=".3" />
                                    </linearGradient>
                                    <linearGradient id="glassBot" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#c6d5e6" stop-opacity=".25" />
                                        <stop offset="100%" stop-color="#b8cce4" stop-opacity=".45" />
                                    </linearGradient>
                                    <linearGradient id="sandGrad" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#f5ead8" stop-opacity=".85" />
                                        <stop offset="50%" stop-color="#f0e4cc" stop-opacity=".75" />
                                        <stop offset="100%" stop-color="#eddcc0" stop-opacity=".65" />
                                    </linearGradient>
                                    <linearGradient id="capGrad" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#a8b8cc" stop-opacity=".55" />
                                        <stop offset="50%" stop-color="#8a9cb4" stop-opacity=".45" />
                                        <stop offset="100%" stop-color="#a8b8cc" stop-opacity=".55" />
                                    </linearGradient>
                                    <filter id="sandGlow">
                                        <feGaussianBlur stdDeviation="3" result="blur" />
                                        <feMerge>
                                            <feMergeNode in="blur" />
                                            <feMergeNode in="SourceGraphic" />
                                        </feMerge>
                                    </filter>
                                    <clipPath id="clipTop">
                                        <path d="M40,42 C40,42 36,80 36,88 C36,104 58,120 72,128 L90,140 L108,128 C122,120 144,104 144,88 C144,80 140,42 140,42Z" />
                                    </clipPath>
                                    <clipPath id="clipBot">
                                        <path d="M40,238 C40,238 36,200 36,192 C36,176 58,160 72,152 L90,140 L108,152 C122,160 144,176 144,192 C144,200 140,238 140,238Z" />
                                    </clipPath>
                                </defs>
                                ...
                            </svg>
                        </div>
                    </div>
                </section>

                <section class="sec rsvp-section reveal" id="rsvp">
                    <div class="section-wrap narrow rsvp-wrap">
                        <img class="section-symbol section-symbol--ticket" src="assets/img/bck/little_prince_transparent_star.png" alt="" aria-hidden="true">
                        <p class="t-scr">Будьте нашим гостем</p>
                        <h2 class="t-h">Будь ласка, підтвердіть присутність до 01.07.2026</h2>
                        <div class="moon-divider"><span></span><i></i><span></span></div>
                        <p class="rsvp-note">Ваша відповідь допоможе нам продумати вечір так, щоб кожному гостю було тепло, зручно й смачно.</p>
                        <form class="rsvp-form" action="submit_rsvp.php" method="post">
                            <input type="hidden" name="invite_code" value="<?= e($guest->invite_code) ?>">
                            <?php if ($isCoupleInvite): ?>
                                <input type="hidden" name="plus_one" value="1">
                                <input type="hidden" name="plus_one_name" value="<?= e($partnerName) ?>">
                            <?php endif; ?>

                            <fieldset>
                                <?php if ($isCoupleInvite): ?>
                                    <p class="form-hint">Запрошення для пари: <?= e($guest->name) ?><?= $partnerName !== '' ? ' та ' . e($partnerName) : '' ?>.</p>
                                <?php endif; ?>
                                <legend>Чи будете ви присутні?</legend>
                                <label><input type="radio" name="will_attend" value="1" required data-rsvp-yes> Звісно</label>
                                <label><input type="radio" name="will_attend" value="0" required> На жаль, не зможу</label>
                            </fieldset>

                            <div class="rsvp-extra-fields is-hidden" data-rsvp-extra>
                                <?php if ($isCoupleInvite): ?>

                                <?php elseif ($hasOptionalPlusOne): ?>
                                    <fieldset>
                                        <legend>Гості</legend>
                                        <label><input type="radio" name="plus_one" value="0" checked> Буду сам</label>
                                        <label><input type="radio" name="plus_one" value="1"> Буду з +1</label>
                                        <label class="plus-one-name rsvp-stacked-label is-hidden">
                                            Імʼя супутника
                                            <input type="text" name="plus_one_name" autocomplete="name">
                                        </label>
                                    </fieldset>
                                <?php else: ?>
                                    <input type="hidden" name="plus_one" value="0">
                                <?php endif; ?>

                                <label>
                                    <?= e($mainDrinkLabel) ?>
                                    <select name="drink" class="drink-select">
                                        <option value="">❗ Оберіть варіант</option>
                                        <option value="🍷 Вино">🍷 Вино</option>
                                        <option value="🥂 Шампанське">🥂 Шампанське</option>
                                        <option value="🥃 Віскі">🥃 Віскі</option>
                                        <option value="💃 Горілка">💃 Горілка</option>
                                        <option value="🍼 Безалкогольне">🍼 Безалкогольне</option>
                                    </select>
                                </label>

                                <fieldset class="rsvp-toggle-fieldset">
                                    <legend>Чи хотілось би Вам виголосити тост на весіллі?</legend>
                                    <label><input type="radio" name="prepare_toast" value="1"> Так</label>
                                    <label><input type="radio" name="prepare_toast" value="0" checked> Ні</label>
                                </fieldset>

                                <label class="partner-drink<?= $isCoupleInvite ? '' : ' is-hidden' ?>" <?= $isCoupleInvite ? ' data-always-visible="1"' : '' ?>>
                                    Який напій обирає <?= $isCoupleInvite && $partnerName !== '' ? e($partnerName) : 'партнер / супутник' ?>?
                                    <select name="partner_drink" class="drink-select">
                                        <option value="">❗ Оберіть варіант</option>
                                        <option value="🍷 Вино">🍷 Вино</option>
                                        <option value="🥂 Шампанське">🥂 Шампанське</option>
                                        <option value="🥃 Віскі">🥃 Віскі</option>
                                        <option value="💃 Горілка">💃 Горілка</option>
                                        <option value="🍼 Безалкогольне">🍼 Безалкогольне</option>
                                    </select>
                                </label>

                                <label>Пісня, яка змусить Вас вийти на танцпол 🎶<input type="text" name="song_request"></label>
                            </div>

                            <button type="submit" class="btn btn-primary rsvp-submit is-hidden" data-rsvp-submit>Підтвердити</button>
                            <p> Ми використовуємо ваші відповіді лише для організації весілля: підтвердження присутності, посадки гостей, напоїв і трансферу. Дані не передаються третім особам.</p>
                        </form>
                    </div>
                </section>
            </div>
        <?php endif; ?>
    </main>
    <?php if (!empty($rsvpError)): ?>
        <div class="rsvp-modal is-visible" data-rsvp-error-modal>
            <div class="rsvp-modal__backdrop" data-close-rsvp-error></div>
            <div class="rsvp-modal__card" role="dialog" aria-modal="true" aria-labelledby="rsvpErrorTitle">
                <button type="button" class="rsvp-modal__close" data-close-rsvp-error aria-label="Закрити">×</button>
                <p class="rsvp-modal__eyebrow">Упс...</p>
                <h2 id="rsvpErrorTitle">Не вдалося зберегти відповідь</h2>
                <p><?= e($rsvpError) ?></p>
                <button type="button" class="section-action" data-close-rsvp-error>Повернутися до анкети</button>
            </div>
        </div>
    <?php endif; ?>
    <script src="assets/js/invite.js?v=<?= e($inviteScriptVersion) ?>"></script>
</body>

</html>
