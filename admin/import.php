<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$rawGuests = '';
$added = 0;
$skipped = 0;
$errors = [];

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function generateImportInviteCode(): string
{
    do {
        $code = generateInviteCode();
    } while (R::count('guests', 'invite_code = ?', [$code]) > 0);

    return $code;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawGuests = (string)($_POST['guests'] ?? '');

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Сесію форми завершено. Оновіть сторінку і спробуйте ще раз.';
    } elseif (trim($rawGuests) === '') {
        $errors[] = 'Вставте список гостей для імпорту.';
    } else {
        $lines = preg_split('/\R/u', $rawGuests) ?: [];

        foreach ($lines as $index => $line) {
            $lineNumber = $index + 1;
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode(';', $line));

            if (count($parts) !== 6) {
                $skipped++;
                $errors[] = 'Рядок ' . $lineNumber . ': очікується 6 полів через крапку з комою.';
                continue;
            }

            [$name, $phone, $telegram, $guestGroup, $maxPlusOne, $tableNumber] = $parts;

            if ($name === '') {
                $skipped++;
                $errors[] = 'Рядок ' . $lineNumber . ': імʼя гостя обовʼязкове.';
                continue;
            }

            if (!in_array($maxPlusOne, ['0', '1'], true)) {
                $skipped++;
                $errors[] = 'Рядок ' . $lineNumber . ': поле +1 має бути 0 або 1.';
                continue;
            }

            $guest = R::dispense('guests');
            $guest->invite_code = generateImportInviteCode();
            $guest->name = $name;
            $guest->phone = $phone !== '' ? $phone : null;
            $guest->telegram = $telegram !== '' ? $telegram : null;
            $guest->guest_group = $guestGroup !== '' ? $guestGroup : null;
            $guest->max_plus_one = (int)$maxPlusOne;
            $guest->table_number = $tableNumber !== '' ? $tableNumber : null;
            $guest->status = 'invited';

            R::store($guest);
            $added++;
        }
    }
}
?>
<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Імпорт гостей</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <main class="admin-shell">
        <nav class="admin-nav" aria-label="Адмін-меню">
            <a href="dashboard.php">Dashboard</a>
            <a href="guests.php">Гості</a>
            <a class="is-active" href="import.php">Імпорт</a>
            <a href="export.php">Експорт</a>
            <a href="logout.php">Вийти</a>
        </nav>

        <div class="admin-header">
            <div>
                <p class="admin-eyebrow">Wedding Invite Portal</p>
                <h1>Імпорт гостей</h1>
            </div>
            <a class="admin-button admin-button-light" href="guests.php">Назад до списку</a>
        </div>

        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <section class="import-summary">
                <article>
                    <span>Додано</span>
                    <strong><?= $added ?></strong>
                </article>
                <article>
                    <span>Пропущено</span>
                    <strong><?= $skipped ?></strong>
                </article>
            </section>
        <?php endif; ?>

        <?php if ($errors !== []): ?>
            <div class="admin-alert">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="admin-form admin-form-wide import-form" action="import.php" method="post">
            <?= csrfField() ?>
            <label class="admin-full">
                Список гостей
                <textarea name="guests" rows="12" placeholder="Іван Петренко;+380991112233;@ivan;Друзі;1;3&#10;Олена Коваль;+380671112233;@olena;Родина;0;2"><?= e($rawGuests) ?></textarea>
            </label>

            <p class="form-hint admin-full">Формат рядка: Ім'я;Телефон;Telegram;Група;+1;Стіл</p>

            <div class="admin-form-actions">
                <button type="submit">Імпортувати гостей</button>
                <a href="guests.php">Скасувати</a>
            </div>
        </form>
    </main>
</body>
</html>
