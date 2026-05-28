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

if ($hasConfirmedAttendance && !$allowInviteEdit) {
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
    <link rel="stylesheet" href="assets/css/style.css">
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
                        <div class="rose-scene">
                            <span class="rose-scene__stars" aria-hidden="true"></span>
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

                                <rect x="28" y="18" rx="4" ry="4" width="124" height="16" fill="url(#capGrad)" stroke="var(--dusty)" stroke-width="1.2" />
                                <circle cx="38" cy="18" r="3" fill="var(--dusty)" opacity=".2" stroke="var(--dusty)" stroke-width=".6" />
                                <circle cx="90" cy="18" r="3" fill="var(--dusty)" opacity=".2" stroke="var(--dusty)" stroke-width=".6" />
                                <circle cx="142" cy="18" r="3" fill="var(--dusty)" opacity=".2" stroke="var(--dusty)" stroke-width=".6" />
                                <rect x="36" y="34" width="4" height="8" rx="1" fill="var(--dusty)" opacity=".2" stroke="var(--dusty)" stroke-width=".5" />
                                <rect x="140" y="34" width="4" height="8" rx="1" fill="var(--dusty)" opacity=".2" stroke="var(--dusty)" stroke-width=".5" />

                                <path d="M40,42 C40,42 36,80 36,88 C36,104 58,120 72,128 L90,140 L108,128 C122,120 144,104 144,88 C144,80 140,42 140,42" fill="url(#glassTop)" stroke="var(--dusty)" stroke-width="1.4" stroke-linejoin="round" />
                                <path d="M50,50 C48,68 48,82 54,98" stroke="#fff" stroke-width="2" opacity=".35" stroke-linecap="round" />
                                <g clip-path="url(#clipTop)">
                                    <path id="sandTop" fill="url(#sandGrad)" filter="url(#sandGlow)" />
                                </g>

                                <circle cx="78" cy="72" r="18" fill="#fff" opacity=".75" filter="drop-shadow(0 0 8px rgba(255,255,255,.7)) drop-shadow(0 0 16px rgba(212,226,240,.4))" />
                                <circle cx="84" cy="68" r="16" fill="#d4e2f0" opacity=".5" />
                                <path d="M92,58 A18,18 0 1,0 82,90 A14,14 0 0,1 92,58Z" fill="#fff" opacity=".65" filter="drop-shadow(0 0 6px rgba(255,255,255,.6))" />
                                <g fill="#fff" filter="drop-shadow(0 0 3px rgba(255,255,255,.8))">
                                    <path d="M110,62 L111,66 110,70 109,66Z" />
                                    <path d="M106,66 L110,65 114,66 110,67Z" />
                                    <path d="M120,78 L120.8,81 120,84 119.2,81Z" />
                                    <path d="M117,81 L120,80.2 123,81 120,81.8Z" />
                                    <path d="M60,58 L60.6,60.5 60,63 59.4,60.5Z" />
                                    <path d="M57.5,60.5 L60,60 62.5,60.5 60,61Z" />
                                </g>

                                <path class="sand-stream sand-stream-main" d="M90,130 L90,180" />
                                <path class="sand-stream sand-stream-glow" d="M90,132 L90,178" />
                                <g class="falling-stars" fill="#fff">
                                    <circle r="1.5">
                                        <animate attributeName="opacity" values=".8;.3;0" dur="1.3s" repeatCount="indefinite" begin=".2s" />
                                        <animateTransform attributeName="transform" type="translate" values="90,134;87,165;86,196" dur="1.3s" repeatCount="indefinite" begin=".2s" />
                                    </circle>
                                    <circle r="1.2">
                                        <animate attributeName="opacity" values=".7;.25;0" dur="1.1s" repeatCount="indefinite" begin=".8s" />
                                        <animateTransform attributeName="transform" type="translate" values="90,136;93,168;94,198" dur="1.1s" repeatCount="indefinite" begin=".8s" />
                                    </circle>
                                    <circle r="1">
                                        <animate attributeName="opacity" values=".6;.2;0" dur=".9s" repeatCount="indefinite" begin="1.3s" />
                                        <animateTransform attributeName="transform" type="translate" values="90,135;89,170;90,200" dur=".9s" repeatCount="indefinite" begin="1.3s" />
                                    </circle>
                                </g>
                                <circle cx="90" cy="140" r="6" fill="#fff" opacity=".15">
                                    <animate attributeName="r" values="4;14;4" dur="3s" repeatCount="indefinite" />
                                    <animate attributeName="opacity" values=".1;.3;.1" dur="3s" repeatCount="indefinite" />
                                </circle>

                                <path d="M40,238 C40,238 36,200 36,192 C36,176 58,160 72,152 L90,140 L108,152 C122,160 144,176 144,192 C144,200 140,238 140,238" fill="url(#glassBot)" stroke="var(--dusty)" stroke-width="1.4" stroke-linejoin="round" />
                                <path d="M50,230 C48,214 48,200 52,186" stroke="#fff" stroke-width="2" opacity=".3" stroke-linecap="round" />
                                <g clip-path="url(#clipBot)">
                                    <path id="sandBot" fill="url(#sandGrad)" filter="url(#sandGlow)" />
                                </g>
                                <g fill="#fff" filter="drop-shadow(0 0 3px rgba(255,255,255,.8))">
                                    <path d="M80,178 L80.8,181 80,184 79.2,181Z" />
                                    <path d="M77,181 L80,180.2 83,181 80,181.8Z" />
                                    <path d="M105,185 L105.6,187.5 105,190 104.4,187.5Z" />
                                    <path d="M102.5,187.5 L105,187 107.5,187.5 105,188Z" />
                                </g>
                                <rect x="28" y="238" rx="4" ry="4" width="124" height="16" fill="url(#capGrad)" stroke="var(--dusty)" stroke-width="1.2" />
                                <rect x="36" y="232" width="4" height="6" rx="1" fill="var(--dusty)" opacity=".2" stroke="var(--dusty)" stroke-width=".5" />
                                <rect x="140" y="232" width="4" height="6" rx="1" fill="var(--dusty)" opacity=".2" stroke="var(--dusty)" stroke-width=".5" />
                                <circle cx="38" cy="258" r="3.5" fill="var(--dusty)" opacity=".2" stroke="var(--dusty)" stroke-width=".6" />
                                <circle cx="90" cy="258" r="3.5" fill="var(--dusty)" opacity=".2" stroke="var(--dusty)" stroke-width=".6" />
                                <circle cx="142" cy="258" r="3.5" fill="var(--dusty)" opacity=".2" stroke="var(--dusty)" stroke-width=".6" />
                            </svg>
                        </div>
                    </div>
                </section>

                <!-- ══ LOCATION ══ -->
                <section class="sec sec--w location-section reveal" id="location">
                    <div class="wl">
                        <p class="t-scr">Локація</p>
                        <h2 class="t-h">Координати весілля</h2>
                        <div class="moon-divider"><span></span><i></i><span></span></div>

                        <div class="location-cards">
                            <article class="location-card">
                                <div class="location-card__icon" aria-hidden="true">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--dusty)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="8" width="20" height="14" rx="2" />
                                        <path d="M2 8l10-6 10 6" />
                                        <path d="M6 22V12h4v10" />
                                        <path d="M14 22V12h4v10" />
                                        <circle cx="12" cy="14" r="2" fill="var(--sky-soft)" />
                                    </svg>
                                </div>
                                <h3>Петрівський Бровар</h3>
                                <p class="location-card__type">З нетерпінням чекаємо на Вас</p>
                                <div class="location-card__meta">
                                    <p>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--dusty)" stroke-width="2" aria-hidden="true">
                                            <circle cx="12" cy="12" r="10" />
                                            <path d="M12 6v6l4 2" />
                                        </svg>
                                        <?= e($ticketStartTime) ?>
                                    </p>
                                    <p>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--dusty)" stroke-width="2" aria-hidden="true">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                            <circle cx="12" cy="10" r="3" />
                                        </svg>
                                        Бровари, Київська область
                                    </p>
                                </div>
                                <a class="btn btn-o" href="https://maps.app.goo.gl/W17bXceU78X7ecWGA" target="_blank" rel="noreferrer">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                    Відкрити у Картах
                                </a>
                                <br>
                                <a class="btn btn-o" href="<?= e($calendarUrl) ?>" target="_blank" rel="noreferrer">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <rect x="3" y="4" width="18" height="18" rx="2" />
                                        <path d="M16 2v4" />
                                        <path d="M8 2v4" />
                                        <path d="M3 10h18" />
                                    </svg>
                                    Додати до календаря
                                </a>
                            </article>
                        </div>
                    </div>
                </section>

                <!-- ══ PROGRAM ══ -->
                <section class="sec sec--ivory program-section reveal" id="program">
                    <div class="prog-stars" aria-hidden="true">
                        <img class="prog-moon prog-moon--one" src="assets/img/moon.webp" alt="">
                        <img class="prog-moon prog-moon--two" src="assets/img/moon.webp" alt="">
                        <img class="prog-moon prog-moon--three" src="assets/img/moon.webp" alt="">
                        <img class="prog-moon prog-moon--four" src="assets/img/moon.webp" alt="">
                        <img class="prog-moon prog-moon--five" src="assets/img/moon.webp" alt="">
                        <div class="stardust white" style="bottom:85%;left:65%;--sz:1.5px;--dur:7s;--del:0s;--drift:-12px"></div>
                        <div class="stardust" style="bottom:78%;left:42%;--sz:1px;--dur:9s;--del:1s;--drift:10px"></div>
                        <div class="stardust white" style="bottom:72%;left:78%;--sz:2px;--dur:6s;--del:2.5s;--drift:-8px"></div>
                        <div class="stardust" style="bottom:65%;left:55%;--sz:1.5px;--dur:8s;--del:.5s;--drift:15px"></div>
                        <div class="stardust white" style="bottom:60%;left:30%;--sz:1px;--dur:10s;--del:3s;--drift:-18px"></div>
                        <div class="stardust" style="bottom:48%;left:20%;--sz:1px;--dur:9.5s;--del:4s;--drift:-10px"></div>
                        <div class="stardust white" style="bottom:35%;left:38%;--sz:1px;--dur:11s;--del:5s;--drift:-15px"></div>
                        <div class="stardust" style="bottom:28%;left:60%;--sz:2px;--dur:8.5s;--del:3.5s;--drift:8px"></div>
                        <div class="mist"></div>
                        <div class="hero-star hero-star--one"></div>
                        <div class="hero-star hero-star--two"></div>
                        <svg class="sparkle-x sparkle-x--one" viewBox="0 0 10 10">
                            <line x1="5" y1="0" x2="5" y2="10" />
                            <line x1="0" y1="5" x2="10" y2="5" />
                        </svg>
                        <svg class="sparkle-x sparkle-x--two" viewBox="0 0 10 10">
                            <line x1="5" y1="0" x2="5" y2="10" />
                            <line x1="0" y1="5" x2="10" y2="5" />
                        </svg>
                    </div>

                    <div class="wl program-wrap">
                        <img class="section-symbol section-symbol--plane" src="assets/img/bck/airplane.png" alt="" aria-hidden="true">
                        <p class="t-scr">Програма дня</p>
                        <h2 class="t-h">Як ми проведемо цей день разом</h2>
                        <div class="moon-divider"><span></span><i></i><span></span></div>

                        <div class="constellation-wrap" style="--program-count: <?= count($programItems) ?>">
                            <div class="program-route">
                                <?php foreach ($programItems as $index => $item): ?>
                                    <?php
                                    $planetSeed = abs(crc32((string)$item['event_time'] . (string)$item['title'] . $index));
                                    $planetSize = 90 + ($planetSeed % 71);
                                    $planetRotate = -32 + (($planetSeed >> 8) % 65);
                                    ?>
                                    <article class="program-point" style="--i: <?= $index ?>; --planet-size: <?= $planetSize ?>px; --planet-rotate: <?= $planetRotate ?>deg;">
                                        <img class="program-planet" src="assets/img/bck/little_prince_transparent_planet.png" alt="" aria-hidden="true">
                                        <p><?= e((string)$item['event_time']) ?></p>
                                        <h3><?= e((string)$item['title']) ?></h3>
                                        <?php if (!empty($item['description'])): ?>
                                            <small><?= e((string)$item['description']) ?></small>
                                        <?php endif; ?>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="sec sec--w dress-section reveal" id="dresscode">
                    <div class="section-wrap narrow dress-wrap">
                        <p class="t-scr">Дрес-код</p>
                        <div class="moon-divider"><span></span><i></i><span></span></div>

                        <div class="dress">
                            <p class="dress__text">
                                Дрес-код у нас один: бути собою. Якщо хочеться підтримати настрій вечора — ми будемо раді ніжним пастельним кольорам.</p>
                            <div class="dress__pal" aria-label="Кольори дрес-коду">
                                <span style="--pal:#f1dbe1" title="Пудровий"></span>
                                <span style="--pal:#b8cce4" title="Небесно-блакитний"></span>
                                <span style="--pal:#e7e8ea" title="Світло-сірий"></span>
                                <span style="--pal:#d8e7d2" title="Пастельно-зелений"></span>
                                <span style="--pal:#ead9c8" title="Персиковий"></span>
                                <span style="--pal:#e7d8f0" title="Лавандовий"></span>
                                <span style="--pal:#d8eceb" title="Мʼятний"></span>
                            </div>
                            <p class="dress__note">Головне присутність, а не вигляд</p>
                        </div>
                    </div>
                </section>

                <section class="sec sec--ivory gifts-section reveal" id="gifts">
                    <div class="section-wrap narrow gifts-wrap">
                        <p class="t-scr">Альтернатива квітам</p>
                        <h2 class="t-h">Найкращий подарунок для нас —
                            це ваша присутність і усмішка.</h2>
                        <div class="moon-divider"><span></span><i></i><span></span></div>

                        <div class="gifts-dome">
                            <img src="assets/img/dome.webp" alt="" aria-hidden="true">
                            <div class="gifts-dome__content">
                                <div class="gift">

                                    <p class="gift__p">
                                        Але якщо Ви хочете зробити нам приємне —
                                        замість квітів принесіть, будь ласка, смаколик або іграшку для тваринки.
                                        Після весілля ми особисто відвеземо всі дарунки до притулку.🏡🐕🐈
                                    </p>
                                    <p class="gift__p">
                                        Нехай цей день запам'ятається не лише нам, а й тим, хто чекає на свою порцію турботи. 🐾
                                    </p>
                                </div>


                            </div>
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

                <section class="sec final-thought reveal">
                    <div class="section-wrap narrow">
                        <p>Найважливіше — невидиме для очей</p>
                        <h2>Тому ми будемо щасливі бачити поруч тих, кого відчуваємо серцем.</h2>
                    </div>
                </section>

                <footer class="invite-footer">
                    <p>Серед мільйонів зірок створюється нове сузірʼя — наша сімʼя</p>
                    <h2>Ростислав & Катерина</h2>
                    <span>1 серпня 2026</span>
                </footer>
            </div>
        <?php endif; ?>
    </main>
    <?php
    $nameAudio = 'main.mp3';
    $hasAudio = is_file(__DIR__ . '/assets/audio/' . $nameAudio);
    if ($hasAudio): ?>
        <button class="music-btn" type="button" aria-label="Музика" data-music-toggle>
            <svg class="icon-play" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M8 5v14l11-7z" />
            </svg>
            <svg class="icon-pause" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M6 19h4V5H6zm8-14v14h4V5z" />
            </svg>
            <span>Для вайбу</span>
        </button>
        <audio data-bg-music data-start-at="33.25" preload="none">
            <source src="assets/audio/<?php echo $nameAudio; ?>" type="audio/mpeg">
        </audio>
    <?php endif; ?>


    <?php if (!empty($rsvpError)): ?>
        <div class="rsvp-modal is-visible" data-rsvp-error-modal>
            <div class="rsvp-modal__backdrop" data-close-rsvp-error></div>

            <div class="rsvp-modal__card" role="dialog" aria-modal="true" aria-labelledby="rsvpErrorTitle">
                <button type="button" class="rsvp-modal__close" data-close-rsvp-error aria-label="Закрити">×</button>

                <p class="rsvp-modal__eyebrow">Упс...</p>
                <h2 id="rsvpErrorTitle">Не вдалося зберегти відповідь</h2>
                <p><?= e($rsvpError) ?></p>

                <button type="button" class="section-action" data-close-rsvp-error>
                    Повернутися до анкети
                </button>
            </div>
        </div>
    <?php endif; ?>
    <script src="assets/js/invite.js"></script>
</body>

</html>