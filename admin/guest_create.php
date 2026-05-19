<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$errors = [];
$form = [
    'name' => '',
    'phone' => '',
    'email' => '',
    'telegram' => '',
    'guest_group' => '',
    'max_plus_one' => '0',
    'table_number' => '',
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($form as $key => $value) {
        $form[$key] = trim((string)($_POST[$key] ?? $value));
    }

    $form['max_plus_one'] = isset($_POST['max_plus_one']) ? '1' : '0';

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Сесію форми завершено. Оновіть сторінку і спробуйте ще раз.';
    }

    if ($form['name'] === '') {
        $errors[] = 'Вкажіть імʼя гостя.';
    }

    if ($form['email'] !== '' && !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Вкажіть коректний email.';
    }

    if ($errors === []) {
        $guest = R::dispense('guests');
        $guest->invite_code = generateUniqueInviteCode();
        $guest->name = $form['name'];
        $guest->phone = $form['phone'] !== '' ? $form['phone'] : null;
        $guest->email = $form['email'] !== '' ? $form['email'] : null;
        $guest->telegram = $form['telegram'] !== '' ? $form['telegram'] : null;
        $guest->guest_group = $form['guest_group'] !== '' ? $form['guest_group'] : null;
        $guest->max_plus_one = (int)$form['max_plus_one'];
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
                Номер столу
                <input type="text" name="table_number" value="<?= e($form['table_number']) ?>">
            </label>

            <label class="admin-checkbox">
                <input type="checkbox" name="max_plus_one" value="1" <?= $form['max_plus_one'] === '1' ? 'checked' : '' ?>>
                Може взяти +1
            </label>

            <div class="admin-form-actions">
                <button type="submit">Зберегти гостя</button>
                <a href="guests.php">Скасувати</a>
            </div>
        </form>
    </main>
</body>
</html>
