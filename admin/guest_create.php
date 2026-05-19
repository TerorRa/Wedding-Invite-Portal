<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$errors = [];
$form = [
    'name' => '',
    'personal_greeting' => '',
    'phone' => '',
    'email' => '',
    'telegram' => '',
    'guest_group' => '',
    'invitation_type' => 'single',
    'plus_one_name' => '',
    'table_number' => '',
];

$invitationTypes = [
    'single' => 'Одна людина без +1',
    'single_plus_one' => 'Одна людина може взяти +1',
    'couple' => 'Сімейна пара',
];

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function generateUniqueInviteCode(): string
{
    do {
        $code = generateInviteCode();
    } while (R::count('guests', 'invite_code = ?', [$code]) > 0);

    return $code;
}

function generateUniqueTicketNumber(string $inviteCode): string
{
    $number = generateTicketNumber($inviteCode);

    while (R::count('guests', 'ticket_number = ?', [$number]) > 0) {
        $number = generateTicketNumber($inviteCode . random_int(1000, 9999));
    }

    return $number;
}

function maxPlusOneForInvitationType(string $type): int
{
    return in_array($type, ['single_plus_one', 'couple'], true) ? 1 : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($form as $key => $value) {
        $form[$key] = trim((string)($_POST[$key] ?? $value));
    }

    if (!array_key_exists($form['invitation_type'], $invitationTypes)) {
        $errors[] = 'Оберіть коректний тип запрошення.';
    }

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Сесію форми завершено. Оновіть сторінку і спробуйте ще раз.';
    }

    if ($form['name'] === '') {
        $errors[] = 'Вкажіть імʼя гостя.';
    }

    if ($form['email'] !== '' && !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Вкажіть коректний email.';
    }

    if ($form['invitation_type'] === 'couple' && $form['plus_one_name'] === '') {
        $errors[] = 'Для сімейної пари вкажіть імʼя партнера.';
    }

    if ($errors === []) {
        $guest = R::dispense('guests');
        $guest->invite_code = generateUniqueInviteCode();
        $guest->ticket_number = generateUniqueTicketNumber((string)$guest->invite_code);
        $guest->name = $form['name'];
        $guest->personal_greeting = $form['personal_greeting'] !== '' ? $form['personal_greeting'] : null;
        $guest->phone = $form['phone'] !== '' ? $form['phone'] : null;
        $guest->email = $form['email'] !== '' ? $form['email'] : null;
        $guest->telegram = $form['telegram'] !== '' ? $form['telegram'] : null;
        $guest->guest_group = $form['guest_group'] !== '' ? $form['guest_group'] : null;
        $guest->invitation_type = $form['invitation_type'];
        $guest->max_plus_one = maxPlusOneForInvitationType($form['invitation_type']);
        $guest->plus_one_name = $form['invitation_type'] === 'couple' ? $form['plus_one_name'] : null;
        $guest->table_number = $form['table_number'] !== '' ? $form['table_number'] : null;
        $guest->status = 'invited';

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
    <title>Новий гість</title>
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
                <h1>Новий гість</h1>
            </div>
            <a class="admin-button admin-button-light" href="guests.php">Назад до списку</a>
        </div>

        <?php if ($errors !== []): ?>
            <div class="admin-alert">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="admin-form" action="guest_create.php" method="post">
            <?= csrfField() ?>
            <label>
                Імʼя
                <input type="text" name="name" value="<?= e($form['name']) ?>" required>
            </label>

            <label class="admin-full">
                Персональне звернення
                <textarea name="personal_greeting" rows="3" placeholder="Наприклад: Дорогі друзі, будемо щасливі бачити вас поруч у цей день."><?= e($form['personal_greeting']) ?></textarea>
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
                Тип запрошення
                <select name="invitation_type">
                    <?php foreach ($invitationTypes as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $form['invitation_type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Імʼя партнера
                <input type="text" name="plus_one_name" value="<?= e($form['plus_one_name']) ?>" placeholder="Для сімейної пари">
            </label>

            <label>
                Номер столу
                <input type="text" name="table_number" value="<?= e($form['table_number']) ?>">
            </label>

            <div class="admin-form-actions">
                <button type="submit">Зберегти гостя</button>
                <a href="guests.php">Скасувати</a>
            </div>
        </form>
    </main>
</body>
</html>
