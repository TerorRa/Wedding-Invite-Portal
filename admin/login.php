<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim((string)($_POST['login'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Сесію форми завершено. Оновіть сторінку і спробуйте ще раз.';
    } elseif ($login === '' || $password === '') {
        $error = 'Введіть логін і пароль.';
    } else {
        $admin = R::findOne('admin_users', 'login = ?', [$login]);

        if ($admin !== null && password_verify($password, (string)$admin->password_hash)) {
            $_SESSION['admin_id'] = (int)$admin->id;
            header('Location: dashboard.php');
            exit;
        }

        $error = 'Неправильний логін або пароль.';
    }
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Вхід в адмін-панель</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <main class="admin-login-shell">
        <form class="admin-login-form" action="login.php" method="post">
            <?= csrfField() ?>
            <p class="admin-eyebrow">Wedding Invite Portal</p>
            <h1>Вхід в адмін-панель</h1>

            <?php if ($error !== ''): ?>
                <div class="admin-alert"><?= e($error) ?></div>
            <?php endif; ?>

            <label>
                Логін
                <input type="text" name="login" value="<?= e($login) ?>" autocomplete="username" required>
            </label>

            <label>
                Пароль
                <input type="password" name="password" autocomplete="current-password" required>
            </label>

            <button type="submit">Увійти</button>
        </form>
    </main>
</body>
</html>
