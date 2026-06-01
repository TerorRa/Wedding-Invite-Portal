<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$code = trim((string)($_GET['code'] ?? ''));
$guest = $code !== '' ? R::findOne('guests', 'invite_code = ?', [$code]) : null;

if ($guest !== null) {
    logInviteAction((int)$guest->id, 'viewed_start');
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

function isRingPhoto(string $filename): bool
{
    $name = strtolower(pathinfo($filename, PATHINFO_FILENAME));

    return in_array($name, ['ring', 'rings', 'obruchka', 'obruchky', 'wedding-ring'], true)
        || str_contains($name, 'ring')
        || str_contains($name, 'obruch');
}

function stableRandomInt(string $seed, int $min, int $max): int
{
    $hash = (int)sprintf('%u', crc32($seed));

    return $min + ($hash % ($max - $min + 1));
}

function stablePhotoPlacement(string $filename, int $index): array
{
    $seed = basename($filename) . '|' . $index;

    $zones = [
        ['left' => [5, 16], 'top' => [8, 24]],
        ['left' => [25, 37], 'top' => [5, 18]],
        ['left' => [53, 65], 'top' => [5, 18]],
        ['left' => [74, 84], 'top' => [8, 24]],
        ['left' => [4, 15], 'top' => [34, 50]],
        ['left' => [74, 84], 'top' => [34, 50]],
        ['left' => [6, 18], 'top' => [60, 76]],
        ['left' => [28, 40], 'top' => [67, 80]],
        ['left' => [52, 64], 'top' => [67, 80]],
        ['left' => [74, 84], 'top' => [60, 76]],
        ['left' => [17, 29], 'top' => [28, 44]],
        ['left' => [61, 73], 'top' => [28, 44]],
    ];

    $zoneIndex = $index % count($zones);
    $zone = $zones[$zoneIndex];
    $cycle = intdiv($index, count($zones));
    $cycleNudge = min(6, $cycle * 3);
    $leftMin = min($zone['left'][1], $zone['left'][0] + $cycleNudge);
    $leftMax = max($leftMin, $zone['left'][1] - $cycleNudge);
    $topMin = min($zone['top'][1], $zone['top'][0] + $cycleNudge);
    $topMax = max($topMin, $zone['top'][1] - $cycleNudge);

    $startXSign = stableRandomInt($seed . '|x-sign', 0, 1) === 1 ? 1 : -1;
    $startYSign = stableRandomInt($seed . '|y-sign', 0, 1) === 1 ? 1 : -1;
    $startRSign = stableRandomInt($seed . '|r-sign', 0, 1) === 1 ? 1 : -1;
    $endRSign = stableRandomInt($seed . '|er-sign', 0, 1) === 1 ? 1 : -1;

    return [
        'left' => stableRandomInt($seed . '|left', $leftMin, $leftMax),
        'top' => stableRandomInt($seed . '|top', $topMin, $topMax),
        'startX' => $startXSign * stableRandomInt($seed . '|sx', 24, 64),
        'startY' => $startYSign * stableRandomInt($seed . '|sy', 24, 62),
        'startR' => $startRSign * stableRandomInt($seed . '|sr', 20, 46),
        'endR' => $endRSign * stableRandomInt($seed . '|er', 3, 11),
        'size' => stableRandomInt($seed . '|size', 94, 112),
    ];
}

$styleVersion = (string)(@filemtime(__DIR__ . '/assets/css/start.css') ?: time());
$dynamicStyleVersion = (string)(@filemtime(__DIR__ . '/assets/css/start-dynamic.css') ?: time());
$scriptVersion = (string)(@filemtime(__DIR__ . '/assets/js/start.js') ?: time());

$inviteUrl = 'invite.php' . ($code !== '' ? '?code=' . urlencode($code) : '');
$aboutUrl = 'about.php' . ($code !== '' ? '?code=' . urlencode($code) : '');

$introDirectory = __DIR__ . '/assets/intro';
$introPhotos = [];
$ringPhoto = null;

if (is_dir($introDirectory)) {
    $files = glob($introDirectory . '/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE) ?: [];
    sort($files, SORT_NATURAL | SORT_FLAG_CASE);

    foreach ($files as $file) {
        if (!is_file($file)) {
            continue;
        }

        $relativePath = 'assets/intro/' . basename($file);

        if (isRingPhoto(basename($file))) {
            $ringPhoto ??= $relativePath;
            continue;
        }

        $introPhotos[] = $relativePath;
    }
}

if ($ringPhoto === null && $introPhotos !== []) {
    $ringPhoto = array_pop($introPhotos);
}

$photoCount = count($introPhotos);
$cardStepSeconds = 0.52;
$ringDelaySeconds = max(2.6, 1.15 + ($photoCount * $cardStepSeconds));
$finalDelaySeconds = $ringDelaySeconds + 1.25;
$introDurationMs = (int)(($finalDelaySeconds + 1.4) * 1000);

$guestName = $guest !== null ? trim((string)$guest->name) : '';
?>
<!doctype html>
<html lang="uk" class="no-js start-root">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ростислав & Катерина — запрошення</title>
    <meta name="description" content="Персональне весільне запрошення Ростислава та Катерини">
    <meta property="og:title" content="Ростислав & Катерина — запрошення">
    <meta property="og:description" content="У нашій історії починається нова глава">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,400&family=Great+Vibes&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(assetUrl('assets/css/start.css')) ?>?v=<?= e($styleVersion) ?>">
    <link rel="stylesheet" href="<?= e(assetUrl('assets/css/start-dynamic.css')) ?>?v=<?= e($dynamicStyleVersion) ?>">
</head>

<body class="start-page">
    <main class="start-shell" aria-label="Вступ до запрошення">
        <?php if ($guest === null): ?>
            <section class="start-not-found" aria-labelledby="notFoundTitle">
                <p class="start-eyebrow">Запрошення</p>
                <h1 id="notFoundTitle">Запрошення не знайдено</h1>
                <p>Перевірте посилання або зверніться до організаторів.</p>
            </section>
        <?php else: ?>
            <section
                class="intro-scene"
                data-intro-scene
                data-intro-duration="<?= $introDurationMs ?>"
                style="--photo-count: <?= $photoCount ?>; --ring-delay: <?= e(number_format($ringDelaySeconds, 2, '.', '')) ?>s; --final-delay: <?= e(number_format($finalDelaySeconds, 2, '.', '')) ?>s;">
                <div class="start-starfield" aria-hidden="true"></div>
                <div class="start-starbursts" aria-hidden="true"></div>
                <div class="start-comets" aria-hidden="true"></div>
                <img class="intro-moon" src="<?= e(assetUrl('assets/img/bck/little_prince_transparent_moon.png')) ?>" alt="" aria-hidden="true">
                <div class="intro-sky" aria-hidden="true">
                    <span class="intro-star intro-star--one"></span>
                    <span class="intro-star intro-star--two"></span>
                    <span class="intro-star intro-star--three"></span>
                    <span class="intro-orbit intro-orbit--one"></span>
                    <span class="intro-orbit intro-orbit--two"></span>
                </div>

                <button type="button" class="intro-skip" data-skip-intro>Пропустити вступ</button>

                <?php if ($introPhotos !== []): ?>
                    <div class="memory-stage" aria-hidden="true">
                        <?php foreach ($introPhotos as $index => $photo): ?>
                            <?php $placement = stablePhotoPlacement($photo, $index); ?>
                            <figure
                                class="memory-card"
                                style="--i: <?= $index ?>; --left: <?= $placement['left'] ?>; --top: <?= $placement['top'] ?>; --start-x: <?= $placement['startX'] ?>vw; --start-y: <?= $placement['startY'] ?>vh; --start-r: <?= $placement['startR'] ?>deg; --end-r: <?= $placement['endR'] ?>deg; --card-scale: <?= e(number_format($placement['size'] / 100, 2, '.', '')) ?>;">
                                <img src="<?= e(assetUrl($photo)) ?>" alt="" loading="<?= $index < 2 ? 'eager' : 'lazy' ?>" decoding="async" <?= $index === 0 ? 'fetchpriority="high"' : '' ?>>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($ringPhoto !== null): ?>
                    <figure class="ring-card" aria-hidden="true">
                        <img src="<?= e(assetUrl($ringPhoto)) ?>" alt="" loading="eager" decoding="async">
                    </figure>
                <?php endif; ?>

                <div class="intro-final" data-intro-final>
                    <p class="start-eyebrow"><?= $guestName !== '' ? e($guestName) . ',' : 'Дорогий гостю,' ?></p>
                    <div class="intro-copy">
                        <p style="margin:0 0 10px;">Здавна люди вірили, що кожна зірка на небі — це чиясь доля.</p>
                        <p style="margin:0 0 10px;">Дві долі, що знайшли одна одну, зливаються в одне світло — і на небосхилі спалахує нова зірочка.<span class="intro-shine-star" aria-hidden="true"></span></p>
                        <p style="margin:0 0 10px;">Незабаром така з'явиться і в нас.</p>
                        <p style="margin:0;">Хочемо, щоб саме ви були поруч,<br>коли вона засяє вперше. ✨</p>
                    </div>
                    <div class="intro-actions">
                        <a class="intro-btn intro-btn--primary" href="<?= e($inviteUrl) ?>" data-open-start>Відкрити запрошення</a>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>
    <audio data-shine-sound preload="auto">
        <source src="<?= e(assetUrl('assets/audio/shine.mp3')) ?>" type="audio/mpeg">
    </audio>
    <script src="<?= e(assetUrl('assets/js/start.js')) ?>?v=<?= e($scriptVersion) ?>"></script>
</body>

</html>
