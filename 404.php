<?php

declare(strict_types=1);

http_response_code(404);

$basePath = rtrim(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/404.php')), '/\\');
$basePath = $basePath === '' ? '' : $basePath;
?>
<!doctype html>
<html lang="uk" class="no-js">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Сторінку не знайдено</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/assets/css/style.css">
</head>

<body>
    <main class="page-shell pass-shell">
        <div class="cosmic-effects cosmic-effects--pass" aria-hidden="true"></div>
        <section class="welcome reveal pass-declined-card">
            <p class="eyebrow">404</p>
            <h1>Сторінку не знайдено</h1>
            <p>Це посилання не веде до активної сторінки Wedding Invite Portal.</p>
            <a class="section-action pass-about-button" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/">На головну</a>
        </section>
    </main>
    <script src="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/assets/js/invite.js"></script>
</body>

</html>
