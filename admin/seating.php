<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

const DEFAULT_TABLE_COUNT = 7;
const MAX_TABLE_COUNT = 7;

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

$tableDefinitions = [
    1 => [
        'title' => 'Стіл 1',
        'subtitle' => 'Наречені',
        'area_class' => 'hall-table--table-1 hall-table--head',
        'empty_text' => 'Стіл наречених поки порожній.',
    ],
    2 => [
        'title' => 'Стіл 2',
        'subtitle' => 'Гості',
        'area_class' => 'hall-table--table-2',
        'empty_text' => 'На цей стіл ще нікого не призначено.',
    ],
    3 => [
        'title' => 'Стіл 3',
        'subtitle' => 'Гості',
        'area_class' => 'hall-table--table-3',
        'empty_text' => 'На цей стіл ще нікого не призначено.',
    ],
    4 => [
        'title' => 'Стіл 4',
        'subtitle' => 'Гості',
        'area_class' => 'hall-table--table-4',
        'empty_text' => 'На цей стіл ще нікого не призначено.',
    ],
    5 => [
        'title' => 'Стіл 5',
        'subtitle' => 'Гості',
        'area_class' => 'hall-table--table-5',
        'empty_text' => 'На цей стіл ще нікого не призначено.',
    ],
    6 => [
        'title' => 'Стіл 6',
        'subtitle' => 'Гості',
        'area_class' => 'hall-table--table-6',
        'empty_text' => 'На цей стіл ще нікого не призначено.',
    ],
    7 => [
        'title' => 'Стіл 7',
        'subtitle' => 'Гості',
        'area_class' => 'hall-table--table-7',
        'empty_text' => 'На цей стіл ще нікого не призначено.',
    ],
];

$confirmedGuests = R::findAll(
    'guests',
    'status = ? ORDER BY table_number IS NULL DESC, table_number + 0 ASC, guest_group ASC, fullname ASC, name ASC, id ASC',
    ['confirmed']
);

$tableNumbers = array_keys($tableDefinitions);
$tableAssignments = ['' => []];
$tablePeople = ['' => 0];
$totalPeople = 0;
$seatedPeople = 0;

foreach ($tableNumbers as $tableNumber) {
    $tableAssignments[(string)$tableNumber] = [];
    $tablePeople[(string)$tableNumber] = 0;
}

foreach ($confirmedGuests as $guest) {
    $attendance = guestAttendance($guest);

    if ($attendance['count'] < 1) {
        continue;
    }

    $normalizedTableNumber = '';
    $rawTableNumber = trim((string)$guest->table_number);

    if ($rawTableNumber !== '' && ctype_digit($rawTableNumber)) {
        $numericTableNumber = (int)$rawTableNumber;

        if (array_key_exists($numericTableNumber, $tableDefinitions)) {
            $normalizedTableNumber = (string)$numericTableNumber;
        }
    }

    $card = [
        'guest' => $guest,
        'table_number' => $normalizedTableNumber,
        'people_count' => (int)$attendance['count'],
        'names' => $attendance['names'],
        'search' => mb_strtolower(
            implode(' ', [
                implode(' ', $attendance['names']),
                (string)$guest->fullname,
                (string)$guest->guest_group,
                (string)$guest->ticket_number,
            ]),
            'UTF-8'
        ),
    ];

    $tableAssignments[$normalizedTableNumber][] = $card;
    $tablePeople[$normalizedTableNumber] += (int)$attendance['count'];
    $totalPeople += (int)$attendance['count'];

    if ($normalizedTableNumber !== '') {
        $seatedPeople += (int)$attendance['count'];
    }
}

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
                <p class="seating-header__description">План зали побудований під 7 столів: №1 — для наречених, №2–7 — для гостей. Розташування столів повторює схему зали.</p>
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
                <span>Розсаджено у залі</span>
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
            <p class="admin-muted">Усі столи на плані фіксовані. Перетягуйте картки між столами або змінюйте стіл через список, якщо потрібно швидко пересадити гостей.</p>
        </section>

        <?php if ($totalPeople === 0): ?>
            <div class="admin-alert admin-alert-success">Поки немає підтверджених гостей для розсадки.</div>
        <?php else: ?>
            <section class="hall-layout-shell">
                <div class="hall-layout__legend">
                    <span><strong>1</strong> — стіл наречених</span>
                    <span><strong>2–7</strong> — гостьові столи</span>
                </div>

                <section class="hall-layout" data-seating-board aria-label="План зали">
                    <?php foreach ($tableDefinitions as $tableNumber => $tableDefinition): ?>
                        <?php $cards = $tableAssignments[(string)$tableNumber]; ?>
                        <article class="hall-table seating-table <?= e($tableDefinition['area_class']) ?>" data-table-zone data-table-number="<?= $tableNumber ?>">
                            <div class="hall-table__surface">
                                <p class="admin-eyebrow"><?= e($tableDefinition['subtitle']) ?></p>
                                <h2><?= e($tableDefinition['title']) ?></h2>
                                <strong data-table-count><?= peopleLabel($tablePeople[(string)$tableNumber]) ?></strong>
                            </div>

                            <div class="hall-table__list" data-table-list>
                                <?php foreach ($cards as $card): ?>
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
                                        data-guest-search="<?= e($card['search']) ?>">
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
                                <p class="seating-table__empty" data-table-empty<?= $cards !== [] ? ' hidden' : '' ?>><?= e($tableDefinition['empty_text']) ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
            </section>

            <section class="unassigned-panel seating-print-hide">
                <div class="unassigned-panel__header">
                    <div>
                        <p class="admin-eyebrow">Потрібна увага</p>
                        <h2>Гості без столу</h2>
                    </div>
                    <strong data-table-count><?= peopleLabel($tablePeople['']) ?></strong>
                </div>

                <div class="unassigned-panel__list" data-table-zone data-table-number="">
                    <div class="unassigned-panel__cards" data-table-list>
                        <?php foreach ($tableAssignments[''] as $card): ?>
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
                                data-guest-search="<?= e($card['search']) ?>">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int)$guest->id ?>">
                                <div class="seating-guest-card__main">
                                    <strong><?= e(implode(' та ', $card['names'])) ?></strong>
                                    <span><?= e((string)$guest->fullname) ?></span>
                                    <small><?= e((string)$guest->guest_group) ?><?= trim((string)$guest->ticket_number) !== '' ? ' · ' . e((string)$guest->ticket_number) : '' ?></small>
                                </div>
                                <div class="seating-guest-card__actions">
                                    <span class="seating-party-size"><?= e(peopleLabel($card['people_count'])) ?></span>
                                    <select name="table_number" data-seating-select aria-label="Стіл для <?= e(implode(' та ', $card['names'])) ?>">
                                        <option value="" selected>Без столу</option>
                                        <?php foreach ($tableNumbers as $optionTableNumber): ?>
                                            <option value="<?= $optionTableNumber ?>">Стіл <?= $optionTableNumber ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit">Зберегти</button>
                                </div>
                                <span class="seating-save-status" data-save-status aria-live="polite"></span>
                            </form>
                        <?php endforeach; ?>
                    </div>
                    <p class="seating-table__empty" data-table-empty<?= $tableAssignments[''] !== [] ? ' hidden' : '' ?>>Усі підтверджені гості вже мають свій стіл.</p>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <script src="../assets/js/seating.js"></script>
</body>
</html>
