<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$allowedStatuses = ['all', 'invited', 'opened', 'confirmed', 'declined'];
$status = (string)($_GET['status'] ?? 'all');
$status = in_array($status, $allowedStatuses, true) ? $status : 'all';
$search = trim((string)($_GET['q'] ?? ''));

$where = [];
$params = [];

if ($status !== 'all') {
    $where[] = 'status = ?';
    $params[] = $status;
}

if ($search !== '') {
    $where[] = '(name LIKE ? OR phone LIKE ? OR telegram LIKE ?)';
    $searchLike = '%' . $search . '%';
    $params[] = $searchLike;
    $params[] = $searchLike;
    $params[] = $searchLike;
}

$sql = $where === [] ? 'ORDER BY id DESC' : implode(' AND ', $where) . ' ORDER BY id DESC';
$guests = R::findAll('guests', $sql, $params);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$projectPath = rtrim(dirname(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/Wedding-Invite-Portal/admin/guests.php'))), '/\\');
$projectPath = $projectPath === '' ? '' : $projectPath;

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function inviteUrl(string $scheme, string $host, string $projectPath, string $code): string
{
    return $scheme . '://' . $host . $projectPath . '/invite.php?code=' . urlencode($code);
}

function invitationMessage(string $name, string $link): string
{
    return "Привіт, {$name}! ❤️\n"
        . "Ми підготували для тебе особисте запрошення на наше весілля.\n"
        . "Будемо дуже раді бачити тебе поруч у цей день.\n\n"
        . "Відкрити запрошення:\n"
        . $link . "\n\n"
        . "Ростислав і Катерина";
}

function telegramUrl(?string $telegram): ?string
{
    $username = ltrim(trim((string)$telegram), '@');

    if ($username === '') {
        return null;
    }

    return 'https://t.me/' . rawurlencode($username);
}

function invitationTypeLabel(?string $type, int $maxPlusOne): string
{
    return match ((string)$type) {
        'couple' => 'Пара',
        'single_plus_one' => '1 + можливий +1',
        default => $maxPlusOne === 1 ? '1 + можливий +1' : '1 без +1',
    };
}
?>
<!doctype html>
<html lang="uk">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Гості</title>
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
                <h1>Гості</h1>
            </div>
            <div class="admin-actions">
                <a class="admin-button admin-button-light" href="guest_create.php">Створити гостя</a>
                <a class="admin-button admin-button-light" href="import.php">Імпорт гостей</a>
                <a class="admin-button admin-button-light" href="export.php">Експорт CSV</a>
            </div>
        </div>

        <form class="admin-filters" action="guests.php" method="get">
            <label>
                Статус
                <select name="status">
                    <?php foreach ($allowedStatuses as $option): ?>
                        <option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>>
                            <?= $option === 'all' ? 'всі' : e($option) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Пошук
                <input type="search" name="q" value="<?= e($search) ?>" placeholder="Ім'я, телефон або telegram">
            </label>

            <button type="submit">Знайти</button>
            <a href="guests.php">Скинути</a>
        </form>

        <section class="admin-table-panel">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Квиток</th>
                            <th>Ім'я</th>
                            <th>Телефон</th>
                            <th>Група</th>
                            <th>Тип</th>
                            <th>Статус</th>
                            <th>+1</th>
                            <th>Напій</th>
                            <th>Напій партнера</th>
                            <th>Тост</th>
                            <th>Стіл</th>
                            <th>Відкриття</th>
                            <th>Відповідь</th>
                            <th>Посилання</th>
                            <th>Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($guests === []): ?>
                            <tr>
                                <td colspan="16" class="admin-empty">Гостей не знайдено.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($guests as $guest): ?>
                            <?php $link = inviteUrl($scheme, $host, $projectPath, (string)$guest->invite_code); ?>
                            <?php $telegramLink = telegramUrl($guest->telegram); ?>
                            <tr>
                                <td><?= (int)$guest->id ?></td>
                                <td><?= e($guest->ticket_number) ?></td>
                                <td><?= e($guest->name) ?></td>
                                <td><?= e($guest->phone) ?></td>
                                <td><?= e($guest->guest_group) ?></td>
                                <td><?= e(invitationTypeLabel($guest->invitation_type, (int)$guest->max_plus_one)) ?></td>
                                <td><span class="status-pill"><?= e($guest->status) ?></span></td>
                                <td><?= (int)$guest->plus_one === 1 ? e($guest->plus_one_name ?: 'Так') : 'Ні' ?></td>
                                <td><?= e($guest->drink) ?></td>
                                <td><?= e($guest->partner_drink) ?></td>
                                <td><?= (int)$guest->prepare_toast === 1 ? 'Так' : 'Ні' ?></td>
                                <td><?= e($guest->table_number) ?></td>
                                <td><?= e($guest->opened_at) ?></td>
                                <td><?= e($guest->answered_at) ?></td>
                                <td><a class="table-link" href="<?= e($link) ?>" target="_blank" rel="noreferrer">Відкрити</a></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="guest_edit.php?id=<?= (int)$guest->id ?>">Редагувати</a>
                                        <button
                                            type="button"
                                            class="copy-message-button"
                                            data-copy-message="<?= e(invitationMessage((string)$guest->name, $link)) ?>"
                                        >
                                            Копіювати запрошення
                                        </button>
                                        <?php if ($telegramLink !== null): ?>
                                            <a href="<?= e($telegramLink) ?>" target="_blank" rel="noreferrer">Telegram</a>
                                        <?php endif; ?>
                                        <?php if (!empty($guest->phone)): ?>
                                            <button type="button" class="copy-phone-button" data-copy-phone="<?= e($guest->phone) ?>">
                                                Copy phone
                                            </button>
                                        <?php endif; ?>
                                        <form class="confirm-delete-form" action="guest_delete.php" method="post" data-confirm-message="Видалити гостя?">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="id" value="<?= (int)$guest->id ?>">
                                            <button type="submit">Видалити</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <script src="../assets/js/admin.js"></script>
</body>

</html>
