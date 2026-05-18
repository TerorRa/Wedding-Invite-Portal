<?php

declare(strict_types=1);

http_response_code(404);

$basePath = '/Wedding-Invite-Portal';
?>
<!doctype html>
<html lang="uk" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Сторінку не знайдено</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/style.css">
</head>
<body>
    <main class="page-shell">
        <section class="welcome reveal">
            <p class="eyebrow">404</p>
            <h1>Сторінку не знайдено</h1>
            <p>Це посилання не веде до активної сторінки Wedding Invite Portal.</p>
            <a class="hero-action" href="<?= $basePath ?>/">На головну</a>
        </section>
    </main>
    <script src="<?= $basePath ?>/assets/js/invite.js"></script>
</body>
</html>
