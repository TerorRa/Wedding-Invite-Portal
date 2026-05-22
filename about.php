<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$code = trim((string)($_GET['code'] ?? ''));
$guest = $code !== '' ? R::findOne('guests', 'invite_code = ?', [$code]) : null;

if ($guest !== null) {
    logInviteAction((int)$guest->id, 'viewed_about');
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function assetUrl(string $path): string
{
    $scriptDir = str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '')));
    $basePath = rtrim($scriptDir, '/');

    if ($basePath === '/' || $basePath === '.') {
        $basePath = '';
    }

    return $basePath . '/' . ltrim($path, '/');
}

$styleVersion = (string)(@filemtime(__DIR__ . '/assets/css/style.css') ?: time());
$aboutPhotos = [];
$aboutPhotoDirectory = __DIR__ . '/assets/about';

if (is_dir($aboutPhotoDirectory)) {
    $files = glob($aboutPhotoDirectory . '/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE) ?: [];
    sort($files, SORT_NATURAL);

    foreach ($files as $file) {
        if (is_file($file)) {
            $aboutPhotos[] = 'assets/about/' . basename($file);
        }
    }
}

$ticketUrl = 'ticket.php' . ($code !== '' ? '?code=' . urlencode($code) : '');
$inviteUrl = 'invite.php' . ($code !== '' ? '?code=' . urlencode($code) : '');

$chapters = [
    [
        'title' => 'Початок',
        'text' => 'У кожної сімʼї є своя перша сторінка. Наша почалась із простих моментів, які поступово стали важливішими за будь-які великі плани.',
    ],
    [
        'title' => 'Ближче одне до одного',
        'text' => 'Ми вчилися бачити не тільки свята, а й будні: підтримувати, сміятися, миритися, чути і залишатися поруч.',
    ],
    [
        'title' => 'Наші маршрути',
        'text' => 'Дороги, прогулянки, спільні подорожі й випадкові кадри з часом склалися в одну історію, яку вже неможливо розділити навпіл.',
    ],
    [
        'title' => 'Теплі традиції',
        'text' => 'Ми створювали свої маленькі ритуали: улюблені місця, жарти, пісні, вечори й дрібниці, які роблять дім домом.',
    ],
    [
        'title' => 'Рішення',
        'text' => 'Одного дня стало зрозуміло: це вже не просто спільний шлях. Це бажання будувати майбутнє разом і називати його сімʼєю.',
    ],
    [
        'title' => 'Поруч із вами',
        'text' => 'У нашій історії є люди, без яких вона була б іншою. Саме тому нам так важливо розділити цей день із близькими.',
    ],
    [
        'title' => 'Перед святом',
        'text' => 'Ми зберігаємо хвилювання, радість і передчуття. Попереду день, у якому багато любові, музики, обіймів і світла.',
    ],
    [
        'title' => 'Далі разом',
        'text' => 'Весілля стане не фіналом історії, а її новою главою. І ми дуже хочемо, щоб перші рядки цієї глави були поруч із вами.',
    ],
];

$storyItems = [];

foreach ($aboutPhotos as $index => $photo) {
    $chapter = $chapters[$index] ?? [
        'title' => 'Наша історія',
        'text' => 'Ще один кадр нашого шляху до дня, коли дві дороги стають однією сімейною історією.',
    ];

    $storyItems[] = [
        'photo' => $photo,
        'title' => $chapter['title'],
        'text' => $chapter['text'],
    ];
}
?>
<!doctype html>
<html lang="uk" class="no-js">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Про нас</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,400&family=Great+Vibes&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= e($styleVersion) ?>">
</head>

<body class="about-page about-page--snap">
    <main class="about-snap" aria-label="Наша історія">
        <section class="about-video-hero">
            <video class="about-video-hero__media" autoplay muted loop playsinline preload="metadata" poster="<?= e(assetUrl($storyItems[0]['photo'] ?? 'assets/about/1.jpg')) ?>">
                <source src="<?= e(assetUrl('assets/about/VID_1.mp4')) ?>" type="video/mp4">
            </video>
            <div class="about-video-note about-video-note--left">
                <span>01</span>
                <p>Це був довгий шлях для нас.</p>
            </div>
            <div class="about-video-note about-video-note--right">
                <span>02</span>
                <p>І кожен крок зробив цю історію нашою.</p>
            </div>
        </section>

        <section class="about-title-section" id="aboutStoryStart">
            <div class="about-snap-content about-title-content reveal">
                <p class="t-scr">Ростислав & Катерина</p>
                <h1>Про нас</h1>
                <p>Кілька кадрів про шлях, який привів нас до створення сімʼї.</p>
                <div class="about-actions">
                    <a class="section-action" href="<?= e($ticketUrl) ?>">До квитка</a>
                    <a class="section-action btn-o" href="<?= e($inviteUrl) ?>">До запрошення</a>
                </div>
            </div>
        </section>

        <?php foreach ($storyItems as $index => $item): ?>
            <?php $photoMode = $index % 2 === 0 ? 'fixed' : 'flow'; ?>
            <section class="about-snap-section about-snap-section--<?= e($photoMode) ?>" style="--about-photo: url('<?= e(assetUrl($item['photo'])) ?>');">
                <figure class="about-snap-photo">
                    <img src="<?= e(assetUrl($item['photo'])) ?>" alt="" aria-hidden="true" loading="<?= $index < 2 ? 'eager' : 'lazy' ?>" decoding="async" <?= $index === 0 ? 'fetchpriority="high"' : '' ?>>
                </figure>
                <div class="about-snap-content">
                    <span><?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    <h2><?= e($item['title']) ?></h2>
                    <p><?= e($item['text']) ?></p>
                </div>
            </section>
        <?php endforeach; ?>
    </main>

    <script src="assets/js/invite.js"></script>
</body>

</html>
