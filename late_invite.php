<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$code = trim((string)($_GET['code'] ?? ''));
$guest = $code !== '' ? R::findOne('guests', 'invite_code = ?', [$code]) : null;

if ($guest !== null) {
    logInviteAction((int)$guest->id, 'viewed_late_invite');
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

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
?>
<!doctype html>
<html lang="uk" class="no-js">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Час відповіді минув</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,400&family=Great+Vibes&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= e($styleVersion) ?>">
</head>

<body class="celestial-theme late-page">
    <main class="late-page-shell">
        <section class="late-invite-card">
            <p class="t-scr">Шкода що часу не залишилось</p>

            <?php if ($guest === null): ?>
                <h1 class="t-h">Запрошення не знайдено</h1>
                <p class="late-invite-text">Перевірте посилання або зверніться до організаторів.</p>
            <?php else: ?>
                <h1 class="t-h"><?= e($guest->name) ?>, ми чекали на твою відповідь</h1>
                <p class="late-invite-text">На жаль, ми не отримали відповіді вчасно, але ми завжди раді бачити тебе на нашому святі. Звʼяжися з нами, якщо ти все ж бажаєш бути присутнім.</p>
            <?php endif; ?>

            <?php if ($photoFiles !== []): ?>
                <div class="late-filmstrip" aria-label="Фотоспогади">
                    <div class="late-filmstrip__track">
                        <?php for ($loop = 0; $loop < 2; $loop++): ?>
                            <?php foreach ($photoFiles as $index => $photo): ?>
                                <figure class="late-filmstrip__frame">
                                    <img src="<?= e($photo) ?>" alt="Фото Ростислава та Катерини <?= $index + 1 ?>" loading="eager" decoding="async"<?= $loop === 0 && $index < 4 ? ' fetchpriority="high"' : '' ?>>
                                </figure>
                            <?php endforeach; ?>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>
