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
$drinkOptions = ['Вино', 'Шампанське', 'Віскі', 'Горілка', 'Безалкогольне', 'Інше'];
$flashMessage = (string)($_SESSION['admin_flash'] ?? '');
$flashError = (string)($_SESSION['admin_flash_error'] ?? '');
unset($_SESSION['admin_flash'], $_SESSION['admin_flash_error']);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$projectPath = rtrim(dirname(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/admin/guests.php'))), '/\\');
$projectPath = $projectPath === '' ? '' : $projectPath;
$returnUrl = 'guests.php';

if ((string)($_SERVER['QUERY_STRING'] ?? '') !== '') {
    $returnUrl .= '?' . (string)$_SERVER['QUERY_STRING'];
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function inviteUrl(string $scheme, string $host, string $projectPath, string $code): string
{
    return $scheme . '://' . $host . $projectPath . '/start.php?code=' . urlencode($code);
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
    /*switch ((string)$type) {

        case 'couple':
            return 'Пара';
    
        case 'single_plus_one':
            return '1 + можливий +1';
    
        default:
            return $maxPlusOne == 1
                ? '1 + можливий +1'
                : '1 без +1';
    };*/
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

        <?php if ($flashMessage !== ''): ?>
            <div class="admin-alert admin-alert-success"><?= e($flashMessage) ?></div>
        <?php endif; ?>

        <?php if ($flashError !== ''): ?>
            <div class="admin-alert"><?= e($flashError) ?></div>
        <?php endif; ?>

        <section class="admin-table-panel">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID / Квиток</th>
                            <th>Група / Тип</th>
                            <th>Прізвище / Телефон</th>
                            <th>Ім'я / +1 Партнер</th>
                            <th>Напій / Напій партнера</th>
                            <th>Стіл / Тост</th>
                            <th>Відповідь / Відкриття</th>
                            <th>Статус / Посилання</th>
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
                            <?php $hasDisplayedPlusOne = (int)$guest->plus_one === 1 || (string)$guest->invitation_type === 'couple'; ?>
                            <?php $invitationType = (string)($guest->invitation_type ?: ((int)$guest->max_plus_one === 1 ? 'single_plus_one' : 'single')); ?>
                            <?php $allowsPlusOne = $invitationType === 'single_plus_one' || $invitationType === 'couple' || (int)$guest->max_plus_one === 1; ?>
                            <?php $canAdminConfirm = !in_array((string)$guest->status, ['confirmed', 'declined'], true); ?>
                            <?php $hasAttendanceBreakdown = $guest->primary_attends !== null || $guest->partner_attends !== null; ?>
                            <?php $primaryAttendanceMark = $hasAttendanceBreakdown ? ((int)$guest->primary_attends === 1 ? '🟢 ' : '🔴 ') : ''; ?>
                            <?php $partnerAttendanceMark = $hasAttendanceBreakdown ? ((int)$guest->partner_attends === 1 ? '🟢+ ' : '🔴+ ') : ($hasDisplayedPlusOne ? '🟢+ ' : '🔴Ні '); ?>
                            <?php $statusClass = in_array((string)$guest->status, ['confirmed', 'declined', 'opened'], true) ? ' status-pill--' . (string)$guest->status : ''; ?>
                            <tr>
                                <td>
                                    <?= (int)$guest->id ?>
                                    <br>
                                    <?= e($guest->ticket_number) ?>
                                </td>
                                <td>
                                    <?= e($guest->guest_group) ?>
                                    <br>
                                    <?= e(invitationTypeLabel($guest->invitation_type, (int)$guest->max_plus_one)) ?>
                                </td>
                                     
                                <td><?= e($guest->fullname) ?><br> 
                                    <?= e($guest->phone) ?><br>
                                    <?php if ((int)$guest->is_send === 0): ?>
                                        <a class="table-link" href="guest_send.php?id=<?= (int)$guest->id ?>">❗❓</a>
                                    <?php else: ?>
                                        💌
                                    <?php endif; ?>
                                </td>
                                
                                <td><?= e($primaryAttendanceMark . (string)$guest->name) ?>
                                    <br>
                                    <?= e($partnerAttendanceMark . (string)$guest->plus_one_name) ?>
                                </td>
                                
                                <td><?= e($guest->drink) ?><br> <?= e($guest->partner_drink) ?></td>
                                <td><?= e($guest->table_number) ?> <br>
                                    <?= (int)$guest->prepare_toast === 1 ? '🥂Так' : '💤Ні' ?>
                                </td>

                                <td>
                                    <strong class="answered-at"><?= e($guest->answered_at) ?></strong>
                                    <br>
                                    <?= e($guest->opened_at) ?>
                                </td>
                                <td>
                                    <a class="table-link" href="<?= e($link) ?>" target="_blank" rel="noreferrer">Відкрити</a>
                                    <br>
                                    <span class="status-pill<?= e($statusClass) ?>"><?= e($guest->status) ?></span>
                                    <?php if ($canAdminConfirm): ?>
                                        <br>
                                        <button
                                            type=" button"
                                            class="table-inline-button confirm-guest-button"
                                            data-confirm-guest
                                            data-guest-id="<?= (int)$guest->id ?>"
                                            data-guest-name="<?= e($guest->name) ?>"
                                            data-drink="<?= e($guest->drink) ?>"
                                            data-plus-one="<?= (int)$guest->plus_one ?>"
                                            data-plus-one-name="<?= e($guest->plus_one_name) ?>"
                                            data-partner-drink="<?= e($guest->partner_drink) ?>"
                                            data-allows-plus-one="<?= $allowsPlusOne ? '1' : '0' ?>">
                                            Підтвердити за гостя
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="guest_edit.php?id=<?= (int)$guest->id ?>">Редагувати</a>
                                        <button
                                            type="button"
                                            class="copy-message-button"
                                            data-copy-message="<?= e(invitationMessage((string)$guest->name, $link)) ?>">
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

        <div class="admin-modal" data-confirm-guest-modal aria-hidden="true">
            <div class="admin-modal__backdrop" data-confirm-guest-close></div>
            <section class="admin-modal__card" role="dialog" aria-modal="true" aria-labelledby="confirmGuestTitle">
                <button class="admin-modal__close" type="button" data-confirm-guest-close aria-label="Закрити">×</button>
                <p class="admin-eyebrow">Швидке підтвердження</p>
                <h2 id="confirmGuestTitle">Підтвердити присутність</h2>
                <p class="admin-modal__guest" data-confirm-guest-name></p>

                <form class="admin-confirm-form" action="guest_confirm.php" method="post">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" data-confirm-guest-id>
                    <input type="hidden" name="redirect_to" value="<?= e($returnUrl) ?>">

                    <label>
                        Напій гостя
                        <select name="drink" data-confirm-drink required>
                            <option value="">Оберіть напій</option>
                            <?php foreach ($drinkOptions as $drink): ?>
                                <option value="<?= e($drink) ?>"><?= e($drink) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="admin-checkbox admin-confirm-plus-one-toggle" data-confirm-plus-one-toggle>
                        <input type="checkbox" name="plus_one" value="1" data-confirm-plus-one>
                        Додати +1 / партнера
                    </label>

                    <div class="admin-confirm-plus-one-fields" data-confirm-plus-one-fields>
                        <label>
                            Ім'я +1 / партнера
                            <input type="text" name="plus_one_name" data-confirm-plus-one-name>
                        </label>
                        <label>
                            Напій +1 / партнера
                            <select name="partner_drink" data-confirm-partner-drink>
                                <option value="">Оберіть напій</option>
                                <?php foreach ($drinkOptions as $drink): ?>
                                    <option value="<?= e($drink) ?>"><?= e($drink) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>

                    <div class="admin-form-actions">
                        <button type="submit">Підтвердити присутність</button>
                        <button type="button" class="admin-button admin-button-light" data-confirm-guest-close>Скасувати</button>
                    </div>
                </form>
            </section>
        </div>
    </main>
    <script src="../assets/js/admin.js"></script>
</body>

</html>