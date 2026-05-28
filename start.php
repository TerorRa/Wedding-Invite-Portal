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
<html lang="uk" class="no-js">
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
                style="--photo-count: <?= $photoCount ?>; --ring-delay: <?= e(number_format($ringDelaySeconds, 2, '.', '')) ?>s; --final-delay: <?= e(number_format($finalDelaySeconds, 2, '.', '')) ?>s;"
            >
                <div class="intro-sky" aria-hidden="true">
                    <span class="intro-star intro-star--one"></span>
                    <span class="intro-star intro-star--two"></span>
                    <span class="intro-star intro-star--three"></span>
                    <span class="intro-orbit intro-orbit--one"></span>
                    <span class="intro-orbit intro-orbit--two"></span>
                </div>

                <a class="intro-skip" href="<?= e($inviteUrl) ?>">Пропустити вступ</a>

                <?php if ($introPhotos !== []): ?>
                    <div class="memory-stage" aria-hidden="true">
                        <?php foreach ($introPhotos as $index => $photo): ?>
                            <?php $variant = ($index % 8) + 1; ?>
                            <figure class="memory-card memory-card--<?= $variant ?>" style="--i: <?= $index ?>;">
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
                    <h1>У нашій історії починається нова глава</h1>
                    <p>Ми хочемо запросити вас на день, у якому народиться наша сімʼя.</p>
                    <div class="intro-actions">
                        <a class="intro-btn intro-btn--primary" href="<?= e($inviteUrl) ?>">Відкрити запрошення</a>
                        <a class="intro-btn" href="<?= e($aboutUrl) ?>">Наша історія</a>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>
    <script src="<?= e(assetUrl('assets/js/start.js')) ?>?v=<?= e($scriptVersion) ?>"></script>
</body>
</html>
