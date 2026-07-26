<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

const DEFAULT_TABLE_COUNT = 10;
const MAX_TABLE_COUNT = 20;

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function guestAttendance(object $guest): array
{
    $hasAttendanceBreakdown = $guest->primary_attends !== null || $guest->partner_attends !== null;
    $primaryAttends = $hasAttendanceBreakdown ? (int)$guest->primary_attends : 1;
    $partnerAttends = $hasAttendanceBreakdown ? (int)$guest->partner_attends : (int)$guest->plus_one;
    $names = [];

    if ($primaryAttends === 1) {
        $names[] = trim((string)$guest->name) !== '' ? trim((string)$guest->name) : 'Гість';
    }

    if ($partnerAttends === 1) {
        $partnerName = trim((string)$guest->plus_one_name);
        $names[] = $partnerName !== '' ? $partnerName : 'Гість +1';
    }

    return [
        'count' => $primaryAttends + $partnerAttends,
        'names' => $names,
    ];
}

function peopleLabel(int $count): string
{
    $lastTwo = $count % 100;
    $last = $count % 10;

    if ($lastTwo >= 11 && $lastTwo <= 14) {
        return $count . ' осіб';
    }

    if ($last === 1) {
        return $count . ' особа';
    }

    if ($last >= 2 && $last <= 4) {
        return $count . ' особи';
    }

    return $count . ' осіб';
}

$confirmedGuests = R::findAll(
    'guests',
    'status = ? ORDER BY table_number IS NULL DESC, table_number + 0 ASC, guest_group ASC, fullname ASC, name ASC, id ASC',
    ['confirmed']
);

$tableNumbers = range(1, DEFAULT_TABLE_COUNT);
$guestCards = [];
$totalPeople = 0;
$seatedPeople = 0;

foreach ($confirmedGuests as $guest) {
    $attendance = guestAttendance($guest);

    if ($attendance['count'] < 1) {
        continue;
    }

    $tableNumber = trim((string)$guest->table_number);

    if ($tableNumber !== '' && ctype_digit($tableNumber)) {
        $numericTable = (int)$tableNumber;

        if ($numericTable >= 1 && $numericTable <= MAX_TABLE_COUNT && !in_array($numericTable, $tableNumbers, true)) {
            $tableNumbers[] = $numericTable;
        }
    }

    $guestCards[] = [
        'guest' => $guest,
        'table_number' => $tableNumber,
        'people_count' => (int)$attendance['count'],
        'names' => $attendance['names'],
    ];

    $totalPeople += (int)$attendance['count'];

    if ($tableNumber !== '') {
        $seatedPeople += (int)$attendance['count'];
    }
}

sort($tableNumbers, SORT_NUMERIC);
$unseatedPeople = $totalPeople - $seatedPeople;
$flashMessage = (string)($_SESSION['admin_flash'] ?? '');
$flashError = (string)($_SESSION['admin_flash_error'] ?? '');
unset($_SESSION['admin_flash'], $_SESSION['admin_flash_error']);
?>
<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Розсадка гостей</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/seating.css">
</head>
<body>
    <main class="admin-shell">
        <nav class="admin-nav" aria-label="Адмін-меню">
            <a href="dashboard.php">Dashboard</a>
            <a href="guests.php">Гості</a>
            <a class="is-active" href="seating.php">Розсадка</a>
            <a href="program.php">Програма</a>
            <a href="import.php">Імпорт</a>
            <a href="export.php">Експорт</a>
            <a href="logout.php">Вийти</a>
        </nav>

        <div class="admin-header seating-header">
            <div>
                <p class="admin-eyebrow">Wedding Invite Portal</p>
                <h1>Розсадка гостей</h1>
                <p class="seating-header__description">Перетягуйте підтверджені запрошення між столами або змінюйте номер столу у списку.</p>
            </div>
            <div class="admin-actions seating-print-hide">
                <button class="admin-button admin-button-light" type="button" data-print-seating>Друкувати розсадку</button>
                <a class="admin-button admin-button-light" href="guests.php?status=confirmed">Підтверджені гості</a>
            </div>
        </div>

        <?php if ($flashMessage !== ''): ?>
            <div class="admin-alert admin-alert-success"><?= e($flashMessage) ?></div>
        <?php endif; ?>

        <?php if ($flashError !== ''): ?>
            <div class="admin-alert"><?= e($flashError) ?></div>
        <?php endif; ?>

        <section class="seating-stats" aria-label="Статистика розсадки">
            <article class="stat-card">
                <span>Підтверджено осіб</span>
                <strong data-total-people><?= $totalPeople ?></strong>
            </article>
            <article class="stat-card">
                <span>Розсаджено</span>
                <strong data-seated-people><?= $seatedPeople ?></strong>
            </article>
            <article class="stat-card<?= $unseatedPeople > 0 ? ' seating-stat-warning' : '' ?>">
                <span>Без столу</span>
                <strong data-unseated-people><?= $unseatedPeople ?></strong>
            </article>
        </section>

        <section class="seating-toolbar seating-print-hide">
            <label>
                Пошук гостя
                <input type="search" placeholder="Ім’я, прізвище, група або квиток" data-seating-search>
            </label>
            <p class="admin-muted">Пара або гість із +1 переміщується разом, оскільки номер столу зберігається для всього запрошення.</p>
        </section>

        <?php if ($guestCards === []): ?>
            <div class="admin-alert admin-alert-success">Поки немає підтверджених гостей для розсадки.</div>
        <?php else: ?>
            <section class="seating-board" data-seating-board>
                <article class="seating-table seating-table--unassigned" data-table-zone data-table-number="">
                    <header class="seating-table__header">
                        <div>
                            <p class="admin-eyebrow">Потрібна увага</p>
                            <h2>Без столу</h2>
                        </div>
                        <strong data-table-count><?= peopleLabel($unseatedPeople) ?></strong>
                    </header>
                    <div class="seating-table__list" data-table-list>
                        <?php foreach ($guestCards as $card): ?>
                            <?php if ($card['table_number'] !== '') { continue; } ?>
                            <?php $guest = $card['guest']; ?>
                            <form
                                class="seating-guest-card"
                                action="seating_update.php"
                                method="post"
                                draggable="true"
                                data-seating-card
                                data-guest-id="<?= (int)$guest->id ?>"
                                data-table-number=""
                                data-person-count="<?= $card['people_count'] ?>"
                                data-guest-search="<?= e(mb_strtolower(implode(' ', [implode(' ', $card['names']), (string)$guest->fullname, (string)$guest->guest_group, (string)$guest->ticket_number]), 'UTF-8')) ?>">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int)$guest->id ?>">
                                <div class="seating-guest-card__main">
                                    <strong><?= e(implode(' та ', $card['names'])) ?></strong>
                                    <span><?= e((string)$guest->fullname) ?></span>
                                    <small><?= e((string)$guest->guest_group) ?><?= trim((string)$guest->ticket_number) !== '' ? ' · ' . e((string)$guest->ticket_number) : '' ?></small>
                                </div>
                                <div class="seating-guest-card__actions seating-print-hide">
                                    <span class="seating-party-size"><?= e(peopleLabel($card['people_count'])) ?></span>
                                    <select name="table_number" data-seating-select aria-label="Стіл для <?= e(implode(' та ', $card['names'])) ?>">
                                        <option value="" selected>Без столу</option>
                                        <?php foreach ($tableNumbers as $tableNumber): ?>
                                            <option value="<?= $tableNumber ?>">Стіл <?= $tableNumber ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit">Зберегти</button>
                                </div>
                                <span class="seating-save-status seating-print-hide" data-save-status aria-live="polite"></span>
                            </form>
                        <?php endforeach; ?>
                        <p class="seating-table__empty" data-table-empty>Усі підтверджені гості вже мають стіл.</p>
                    </div>
                </article>

                <?php foreach ($tableNumbers as $tableNumber): ?>
                    <?php
                    $tablePeople = 0;
                    foreach ($guestCards as $card) {
                        if ($card['table_number'] === (string)$tableNumber) {
                            $tablePeople += $card['people_count'];
                        }
                    }
                    ?>
                    <article class="seating-table" data-table-zone data-table-number="<?= $tableNumber ?>">
                        <header class="seating-table__header">
                            <div>
                                <p class="admin-eyebrow">Банкет</p>
                                <h2>Стіл <?= $tableNumber ?></h2>
                            </div>
                            <strong data-table-count><?= peopleLabel($tablePeople) ?></strong>
                        </header>
                        <div class="seating-table__list" data-table-list>
                            <?php foreach ($guestCards as $card): ?>
                                <?php if ($card['table_number'] !== (string)$tableNumber) { continue; } ?>
                                <?php $guest = $card['guest']; ?>
                                <form
                                    class="seating-guest-card"
                                    action="seating_update.php"
                                    method="post"
                                    draggable="true"
                                    data-seating-card
                                    data-guest-id="<?= (int)$guest->id ?>"
                                    data-table-number="<?= $tableNumber ?>"
                                    data-person-count="<?= $card['people_count'] ?>"
                                    data-guest-search="<?= e(mb_strtolower(implode(' ', [implode(' ', $card['names']), (string)$guest->fullname, (string)$guest->guest_group, (string)$guest->ticket_number]), 'UTF-8')) ?>">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= (int)$guest->id ?>">
                                    <div class="seating-guest-card__main">
                                        <strong><?= e(implode(' та ', $card['names'])) ?></strong>
                                        <span><?= e((string)$guest->fullname) ?></span>
                                        <small><?= e((string)$guest->guest_group) ?><?= trim((string)$guest->ticket_number) !== '' ? ' · ' . e((string)$guest->ticket_number) : '' ?></small>
                                    </div>
                                    <div class="seating-guest-card__actions seating-print-hide">
                                        <span class="seating-party-size"><?= e(peopleLabel($card['people_count'])) ?></span>
                                        <select name="table_number" data-seating-select aria-label="Стіл для <?= e(implode(' та ', $card['names'])) ?>">
                                            <option value="">Без столу</option>
                                            <?php foreach ($tableNumbers as $optionTableNumber): ?>
                                                <option value="<?= $optionTableNumber ?>" <?= $optionTableNumber === $tableNumber ? 'selected' : '' ?>>Стіл <?= $optionTableNumber ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit">Зберегти</button>
                                    </div>
                                    <span class="seating-save-status seating-print-hide" data-save-status aria-live="polite"></span>
                                </form>
                            <?php endforeach; ?>
                            <p class="seating-table__empty" data-table-empty>Перетягніть сюди гостя.</p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>

    <script src="../assets/js/seating.js"></script>
</body>
</html>
