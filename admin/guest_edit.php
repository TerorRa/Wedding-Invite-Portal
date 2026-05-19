<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$guest = $id > 0 ? R::load('guests', $id) : null;

if ($guest === null || !$guest->id) {
    http_response_code(404);
    ?>
    <!doctype html>
    <html lang="uk">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Гостя не знайдено</title>
        <link rel="stylesheet" href="../assets/css/admin.css">
    </head>
    <body>
        <main class="admin-shell">
            <div class="admin-alert">Гостя не знайдено.</div>
            <a class="admin-button admin-button-light" href="guests.php">Назад до списку</a>
        </main>
    </body>
    </html>
    <?php
    exit;
}

$errors = [];
$statuses = ['invited', 'opened', 'confirmed', 'declined'];
$willAttendOptions = ['' => 'Не вказано', '1' => 'Так', '0' => 'Ні'];
$drinkOptions = ['', 'Вино', 'Шампанське', 'Віскі', 'Безалкогольне', 'Інше'];

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function emptyToNull(string $value): ?string
{
    return $value === '' ? null : $value;
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$projectPath = rtrim(dirname(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/Wedding-Invite-Portal/admin/guest_edit.php'))), '/\\');
$projectPath = $projectPath === '' ? '' : $projectPath;
$inviteLink = $scheme . '://' . $host . $projectPath . '/invite.php?code=' . urlencode((string)$guest->invite_code);

$form = [
    'name' => (string)$guest->name,
    'phone' => (string)$guest->phone,
    'email' => (string)$guest->email,
    'telegram' => (string)$guest->telegram,
    'guest_group' => (string)$guest->guest_group,
    'max_plus_one' => (string)(int)$guest->max_plus_one,
    'status' => (string)$guest->status,
    'will_attend' => $guest->will_attend === null ? '' : (string)(int)$guest->will_attend,
    'plus_one' => (string)(int)$guest->plus_one,
    'plus_one_name' => (string)$guest->plus_one_name,
    'drink' => (string)$guest->drink,
    'food_notes' => (string)$guest->food_notes,
    'need_transfer' => (string)(int)$guest->need_transfer,
    'song_request' => (string)$guest->song_request,
    'wish' => (string)$guest->wish,
    'table_number' => (string)$guest->table_number,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($form as $key => $value) {
        $form[$key] = trim((string)($_POST[$key] ?? ''));
    }

    $form['max_plus_one'] = isset($_POST['max_plus_one']) ? '1' : '0';
    $form['plus_one'] = isset($_POST['plus_one']) ? '1' : '0';
    $form['need_transfer'] = isset($_POST['need_transfer']) ? '1' : '0';

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Сесію форми завершено. Оновіть сторінку і спробуйте ще раз.';
    }

    if ($form['name'] === '') {
        $errors[] = 'Вкажіть імʼя гостя.';
    }

    if ($form['email'] !== '' && !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Вкажіть коректний email.';
    }

    if (!in_array($form['status'], $statuses, true)) {
        $errors[] = 'Оберіть коректний статус.';
    }

    if (!array_key_exists($form['will_attend'], $willAttendOptions)) {
        $errors[] = 'Оберіть коректне значення участі.';
    }

    if ($errors === []) {
        $guest->name = $form['name'];
        $guest->phone = emptyToNull($form['phone']);
        $guest->email = emptyToNull($form['email']);
        $guest->telegram = emptyToNull($form['telegram']);
        $guest->guest_group = emptyToNull($form['guest_group']);
        $guest->max_plus_one = (int)$form['max_plus_one'];
        $guest->status = $form['status'];
        $guest->will_attend = $form['will_attend'] === '' ? null : (int)$form['will_attend'];
        $guest->plus_one = (int)$form['plus_one'];
        $guest->plus_one_name = emptyToNull($form['plus_one_name']);
        $guest->drink = emptyToNull($form['drink']);
        $guest->food_notes = emptyToNull($form['food_notes']);
        $guest->need_transfer = (int)$form['need_transfer'];
        $guest->song_request = emptyToNull($form['song_request']);
        $guest->wish = emptyToNull($form['wish']);
        $guest->table_number = emptyToNull($form['table_number']);

        R::store($guest);

        header('Location: guests.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Редагування гостя</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <main class="admin-shell">
        <nav class="admin-nav" aria-label="Адмін-меню">
            <a href="dashboard.php">Dashboard</a>
            <a class="is-active" href="guests.php">Гості</a>
            <a href="import.php">Імпорт</a>
            <a href="export.php">Експорт</a>
            <a href="logout.php">Вийти</a>
        </nav>

        <div class="admin-header">
            <div>
                <p class="admin-eyebrow">Wedding Invite Portal</p>
                <h1>Редагування гостя</h1>
            </div>
            <a class="admin-button admin-button-light" href="guests.php">Назад до списку</a>
        </div>

        <section class="copy-link-box">
            <label>
                Персональне посилання
                <input type="text" value="<?= e($inviteLink) ?>" readonly onclick="this.select();">
            </label>
            <a class="admin-button admin-button-light" href="<?= e($inviteLink) ?>" target="_blank" rel="noreferrer">Відкрити</a>
        </section>

        <?php if ($errors !== []): ?>
            <div class="admin-alert">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="admin-form admin-form-wide" action="guest_edit.php" method="post">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= (int)$guest->id ?>">

            <label>
                Імʼя
                <input type="text" name="name" value="<?= e($form['name']) ?>" required>
            </label>

            <label>
                Телефон
                <input type="text" name="phone" value="<?= e($form['phone']) ?>">
            </label>

            <label>
                Email
                <input type="email" name="email" value="<?= e($form['email']) ?>">
            </label>

            <label>
                Telegram
                <input type="text" name="telegram" value="<?= e($form['telegram']) ?>">
            </label>

            <label>
                Група
                <input type="text" name="guest_group" value="<?= e($form['guest_group']) ?>">
            </label>

            <label>
                Статус
                <select name="status">
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?= e($status) ?>" <?= $form['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Участь
                <select name="will_attend">
                    <?php foreach ($willAttendOptions as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $form['will_attend'] === (string)$value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Імʼя супутника
                <input type="text" name="plus_one_name" value="<?= e($form['plus_one_name']) ?>">
            </label>

            <label>
                Напій
                <select name="drink">
                    <?php foreach ($drinkOptions as $drink): ?>
                        <option value="<?= e($drink) ?>" <?= $form['drink'] === $drink ? 'selected' : '' ?>>
                            <?= $drink === '' ? 'Не вказано' : e($drink) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Номер столу
                <input type="text" name="table_number" value="<?= e($form['table_number']) ?>">
            </label>

            <label class="admin-full">
                Обмеження по їжі
                <textarea name="food_notes" rows="3"><?= e($form['food_notes']) ?></textarea>
            </label>

            <label class="admin-full">
                Пісня
                <input type="text" name="song_request" value="<?= e($form['song_request']) ?>">
            </label>

            <label class="admin-full">
                Побажання
                <textarea name="wish" rows="4"><?= e($form['wish']) ?></textarea>
            </label>

            <label class="admin-checkbox">
                <input type="checkbox" name="max_plus_one" value="1" <?= $form['max_plus_one'] === '1' ? 'checked' : '' ?>>
                Може взяти +1
            </label>

            <label class="admin-checkbox">
                <input type="checkbox" name="plus_one" value="1" <?= $form['plus_one'] === '1' ? 'checked' : '' ?>>
                Буде з +1
            </label>

            <label class="admin-checkbox">
                <input type="checkbox" name="need_transfer" value="1" <?= $form['need_transfer'] === '1' ? 'checked' : '' ?>>
                Потрібен трансфер
            </label>

            <div class="admin-form-actions">
                <button type="submit">Зберегти зміни</button>
                <a href="guests.php">Скасувати</a>
            </div>
        </form>
    </main>
</body>
</html>
