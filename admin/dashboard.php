<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$stats = [
    'totalGuests' => (int)R::count('guests'),
    'openedInvites' => (int)R::count('guests', 'status IN (?, ?, ?)', ['opened', 'confirmed', 'declined']),
    'confirmed' => (int)R::count('guests', 'status = ?', ['confirmed']),
    'declined' => (int)R::count('guests', 'status = ?', ['declined']),
    'notAnswered' => (int)R::count('guests', 'status IN (?, ?)', ['invited', 'opened']),
    'plusOnes' => (int)R::count('guests', 'status = ? AND plus_one = ?', ['confirmed', 1]),
];

$stats['expectedPeople'] = $stats['confirmed'] + $stats['plusOnes'];
$drinkStatsRows = R::getAll("
    SELECT drink_name, SUM(drink_count) AS total_count
    FROM (
        SELECT drink AS drink_name, COUNT(*) AS drink_count
        FROM guests
        WHERE status = 'confirmed'
          AND drink IS NOT NULL
          AND drink <> ''
        GROUP BY drink

        UNION ALL

        SELECT partner_drink AS drink_name, COUNT(*) AS drink_count
        FROM guests
        WHERE status = 'confirmed'
          AND plus_one = 1
          AND partner_drink IS NOT NULL
          AND partner_drink <> ''
        GROUP BY partner_drink
    ) AS drinks
    GROUP BY drink_name
    ORDER BY total_count DESC, drink_name ASC
");

$drinkStats = [];

foreach ($drinkStatsRows as $row) {
    $drinkStats[] = [
        'name' => (string)$row['drink_name'],
        'count' => (int)$row['total_count'],
    ];
}
?>
<!doctype html>
<html lang="uk">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Панель керування</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <main class="admin-shell">
        <nav class="admin-nav" aria-label="Адмін-меню">
            <a class="is-active" href="dashboard.php">Dashboard</a>
            <a href="guests.php">Гості</a>
            <a href="program.php">Програма</a>
            <a href="import.php">Імпорт</a>
            <a href="export.php">Експорт</a>
            <a href="logout.php">Вийти</a>
        </nav>

        <div class="admin-header">
            <div>
                <p class="admin-eyebrow">Wedding Invite Portal</p>
                <h1>Панель керування</h1>
            </div>
        </div>

        <section class="stats-grid" aria-label="Статистика гостей">
            <article class="stat-card stat-card-wide">
                <span>Всього гостей</span>
                <strong><?= $stats['totalGuests'] ?></strong>
            </article>
            <article class="stat-card">
                <span>Підтвердили участь</span>
                <strong><?= $stats['confirmed'] ?></strong>
            </article>
            <article class="stat-card">
                <span>Відмовились</span>
                <strong><?= $stats['declined'] ?></strong>
            </article>
            <article class="stat-card">
                <span>Кількість +1</span>
                <strong><?= $stats['plusOnes'] ?></strong>
            </article>
            <article class="stat-card ">
                <span>Загальна очікувана кількість людей</span>
                <strong><?= $stats['expectedPeople'] ?></strong>
            </article>

            <article class="stat-card">
                <span>Відкрили запрошення</span>
                <strong><?= $stats['openedInvites'] ?></strong>
            </article>
            <article class="stat-card">
                <span>Ще не відповіли</span>
                <strong><?= $stats['notAnswered'] ?></strong>
            </article>
            <article class="stat-card stat-card-drinks">
                <span>Кількість осіб по напоям</span>
                <?php if ($drinkStats === []): ?>
                    <p class="admin-muted">Поки немає підтверджених відповідей з обраними напоями.</p>
                <?php else: ?>
                    <div class="drink-stats">
                        <?php foreach ($drinkStats as $drink): ?>
                            <article class="drink-stat">
                                <span><?= htmlspecialchars($drink['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                <strong><?= $drink['count'] ?></strong>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>
        </section>

    </main>
</body>

</html>
