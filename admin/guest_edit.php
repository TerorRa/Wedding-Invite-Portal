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
$drinkOptions = ['', 'Вино', 'Шампанське', 'Віскі', 'Горілка', 'Безалкогольне', 'Інше'];
$invitationTypes = [
    'single' => 'Одна людина без +1',
    'single_plus_one' => 'Одна людина може взяти +1',
    'couple' => 'Сімейна пара',
];
$guestGroupOptions = ['', 'Родичі', 'Друзі', 'Колеги', 'Близькі'];
$tableNumberOptions = array_merge([''], array_map('strval', range(1, 10)));

if (!in_array((string)$guest->guest_group, $guestGroupOptions, true) && (string)$guest->guest_group !== '') {
    $guestGroupOptions[] = (string)$guest->guest_group;
}

if (!in_array((string)$guest->table_number, $tableNumberOptions, true) && (string)$guest->table_number !== '') {
    $tableNumberOptions[] = (string)$guest->table_number;
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function emptyToNull(string $value): ?string
{
    return $value === '' ? null : $value;
}

function maxPlusOneForInvitationType(string $type): int
{
    return in_array($type, ['single_plus_one', 'couple'], true) ? 1 : 0;
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$projectPath = rtrim(dirname(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/Wedding-Invite-Portal/admin/guest_edit.php'))), '/\\');
$projectPath = $projectPath === '' ? '' : $projectPath;
$inviteLink = $scheme . '://' . $host . $projectPath . '/invite.php?code=' . urlencode((string)$guest->invite_code);

$form = [
    'name' => (string)$guest->name,
    'personal_greeting' => (string)$guest->personal_greeting,
    'phone' => (string)$guest->phone,
    'email' => (string)$guest->email,
    'telegram' => (string)$guest->telegram,
    'guest_group' => (string)$guest->guest_group,
    'invitation_type' => (string)($guest->invitation_type ?: ((int)$guest->max_plus_one === 1 ? 'single_plus_one' : 'single')),
    'max_plus_one' => (string)(int)$guest->max_plus_one,
    'status' => (string)$guest->status,
    'will_attend' => $guest->will_attend === null ? '' : (string)(int)$guest->will_attend,
    'plus_one' => (string)(int)$guest->plus_one,
    'plus_one_name' => (string)$guest->plus_one_name,
    'drink' => (string)$guest->drink,
    'partner_drink' => (string)$guest->partner_drink,
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

    $form['plus_one'] = isset($_POST['plus_one']) ? '1' : '0';
    $form['need_transfer'] = isset($_POST['need_transfer']) ? '1' : '0';
    $form['max_plus_one'] = (string)maxPlusOneForInvitationType($form['invitation_type']);

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

    if (!array_key_exists($form['invitation_type'], $invitationTypes)) {
        $errors[] = 'Оберіть коректний тип запрошення.';
    }

    if (!in_array($form['guest_group'], $guestGroupOptions, true)) {
        $errors[] = 'Оберіть коректну групу гостя.';
    }

    if (!in_array($form['table_number'], $tableNumberOptions, true)) {
        $errors[] = 'Оберіть коректний номер столу.';
    }

    if (!array_key_exists($form['will_attend'], $willAttendOptions)) {
        $errors[] = 'Оберіть коректне значення участі.';
    }

    if ($form['invitation_type'] === 'couple' && $form['plus_one_name'] === '') {
        $errors[] = 'Для сімейної пари вкажіть імʼя партнера.';
    }

    if ($errors === []) {
        $guest->name = $form['name'];
        $guest->personal_greeting = emptyToNull($form['personal_greeting']);
        $guest->phone = emptyToNull($form['phone']);
        $guest->email = emptyToNull($form['email']);
        $guest->telegram = emptyToNull($form['telegram']);
        $guest->guest_group = emptyToNull($form['guest_group']);
        $guest->invitation_type = $form['invitation_type'];
        $guest->max_plus_one = (int)$form['max_plus_one'];
        $guest->status = $form['status'];
        $guest->will_attend = $form['will_attend'] === '' ? null : (int)$form['will_attend'];
        $guest->plus_one = (int)$form['plus_one'];
        $guest->plus_one_name = emptyToNull($form['plus_one_name']);
        $guest->drink = emptyToNull($form['drink']);
        $guest->partner_drink = emptyToNull($form['partner_drink']);
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
            <a href="program.php">Програма</a>
            <a href="import.php">Імпорт</a>
            <a href="export.php">Експорт</a>
            <a href="logout.php">Вийти</a>
        </nav>

        <div class="admin-header">
            <div>
                <p class="admin-eyebrow">Wedding Invite Portal</p>
                <h1>Редагування гостя</h1>
            </div>
            <div class="admin-actions">
                <button class="admin-button" type="submit" form="guest-edit-form">Зберегти зміни</button>
                <a class="admin-button admin-button-light" href="guests.php">Назад до списку</a>
            </div>
        </div>

        <section class="copy-link-box">
            <label>
                Персональне посилання
                <input class="copy-select-input" type="text" value="<?= e($inviteLink) ?>" readonly>
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

        <form class="admin-form admin-form-wide" id="guest-edit-form" action="guest_edit.php" method="post">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= (int)$guest->id ?>">

            <label>
                Імʼя
                <input type="text" name="name" value="<?= e($form['name']) ?>" required>
            </label>

            <label class="admin-full">
                Персональне звернення
                <textarea name="personal_greeting" rows="3"><?= e($form['personal_greeting']) ?></textarea>
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
                <select name="guest_group">
                    <?php foreach ($guestGroupOptions as $group): ?>
                        <option value="<?= e($group) ?>" <?= $form['guest_group'] === $group ? 'selected' : '' ?>>
                            <?= $group === '' ? 'Не вказано' : e($group) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Тип запрошення
                <select name="invitation_type">
                    <?php foreach ($invitationTypes as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $form['invitation_type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="form-hint">Для сімейної пари заповніть поле партнера поруч.</span>
            </label>

            <label>
                Імʼя партнера
                <input type="text" name="plus_one_name" value="<?= e($form['plus_one_name']) ?>" placeholder="Для сімейної пари або +1">
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

            <div class="admin-fieldset admin-full">
                <h2>+1 / партнер</h2>
                <div class="admin-fieldset-grid">
                    <label class="admin-checkbox">
                        <input type="checkbox" name="plus_one" value="1" <?= $form['plus_one'] === '1' ? 'checked' : '' ?>>
                        Буде з +1
                    </label>
                </div>
            </div>

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
                Напій партнера / супутника
                <select name="partner_drink">
                    <?php foreach ($drinkOptions as $drink): ?>
                        <option value="<?= e($drink) ?>" <?= $form['partner_drink'] === $drink ? 'selected' : '' ?>>
                            <?= $drink === '' ? 'Не вказано' : e($drink) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Номер столу
                <select name="table_number">
                    <?php foreach ($tableNumberOptions as $tableNumber): ?>
                        <option value="<?= e($tableNumber) ?>" <?= $form['table_number'] === $tableNumber ? 'selected' : '' ?>>
                            <?= $tableNumber === '' ? 'Не вказано' : e($tableNumber) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
