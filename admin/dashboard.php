<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$guests = R::findAll('guests', 'ORDER BY id ASC');
$stats = [
    'totalGuests' => 0,
    'openedInvites' => 0,
    'confirmed' => 0,
    'declined' => 0,
    'notAnswered' => 0,
    'plusOnes' => 0,
    'expectedPeople' => 0,
];
$drinkCounts = [];
$toastList = [];
$songList = [];

foreach ($guests as $guest) {
    $status = (string)$guest->status;
    $invitationType = (string)($guest->invitation_type ?: ((int)$guest->max_plus_one === 1 ? 'single_plus_one' : 'single'));
    $isCouple = $invitationType === 'couple';
    $hasPartnerName = trim((string)$guest->plus_one_name) !== '';
    $invitedPeople = $isCouple && $hasPartnerName ? 2 : 1;
    $hasAttendanceBreakdown = $guest->primary_attends !== null || $guest->partner_attends !== null;
    $song = trim((string)$guest->song_request);
    $toast = ((int)$guest->prepare_toast);

    $stats['totalGuests'] += $invitedPeople;

    if (in_array($status, ['opened', 'confirmed', 'declined'], true)) {
        $stats['openedInvites'] += $invitedPeople;
    }

    if (in_array($status, ['invited', 'opened'], true)) {
        $stats['notAnswered'] += $invitedPeople;
    }

    if ($status === 'confirmed') {
        $primaryAttends = $hasAttendanceBreakdown ? (int)$guest->primary_attends : 1;
        $partnerAttends = $hasAttendanceBreakdown ? (int)$guest->partner_attends : (int)$guest->plus_one;
        $attendingPeople = $primaryAttends + $partnerAttends;

        $stats['confirmed'] += $attendingPeople;
        $stats['plusOnes'] += $partnerAttends;

        if ($song !== '') {
            $songList[] = [
                            'name' => (string)$song
                        ];
        }
        if ($toast === 1) {
            $toastList[] = [
                            'name' => (string)$guest->fullname. ' (' .(string)$guest->name. ' ' . (string)$guest->partner_name . ')'
                        ];
        }

        if ($isCouple) {
            $stats['declined'] += max(0, $invitedPeople - $attendingPeople);
        }

        $drink = trim((string)$guest->drink);
        $partnerDrink = trim((string)$guest->partner_drink);

        if ($primaryAttends === 1 && $drink !== '') {
            $drinkCounts[$drink] = ($drinkCounts[$drink] ?? 0) + 1;
        }

        if ($partnerAttends === 1 && $partnerDrink !== '') {
            $drinkCounts[$partnerDrink] = ($drinkCounts[$partnerDrink] ?? 0) + 1;
        }
    } elseif ($status === 'declined') {
        $stats['declined'] += $invitedPeople;
    }
}

$stats['expectedPeople'] = $stats['confirmed'];
$drinkStats = [];

ksort($drinkCounts, SORT_NATURAL);
arsort($drinkCounts, SORT_NUMERIC);

foreach ($drinkCounts as $drinkName => $drinkCount) {
    $drinkStats[] = [
        'name' => (string)$drinkName,
        'count' => (int)$drinkCount,
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
            <article class="stat-card">
                <span>Всього запрошено гостей</span>
                <strong><?= $stats['totalGuests'] ?></strong>
            </article>
             <!--<article class="stat-card">
                <span>Підтвержено осіб</span>
                <strong><?= $stats['confirmed'] ?></strong>
            </article>-->

            <article class="stat-card">
                <span>Відмовились</span>
                <strong><?= $stats['declined'] ?></strong>
            </article>
            <!--<article class="stat-card">
                <span>Запрошень з +1</span>
                <strong><?= $stats['plusOnes'] ?></strong>
            </article>-->
            <article class="stat-card">
                <span>Відкрили запрошення</span>
                <strong><?= $stats['openedInvites'] ?></strong>
            </article>
            <article class="stat-card">
                <span>Ще не відповіли</span>
                <strong><?= $stats['notAnswered'] ?></strong>
            </article>
            <article class="stat-card stat-card-drinks">
                <span>Статистика по напоям:</span>
                <p class="admin-muted">Підтверджено осіб: <strong><?= $stats['expectedPeople'] ?></strong></p>
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

            <article class="stat-card">
                <span>Обіцяли підготувати тост:</span>
                <?php if ($toastList === []): ?>
                    <p class="admin-muted">Поки немає підтверджених відповідей з обраними тостами.</p>
                <?php else: ?>

                        <?php foreach ($toastList as $toast): ?>
                        <span><?= htmlspecialchars($toast['name'], ENT_QUOTES, 'UTF-8') ?></span>
                            <br>
                        <?php endforeach; ?>

                <?php endif; ?>
            </article>

             <article class="stat-card">
                <span>Пісні, які хочуть почути:</span>
                <?php if ($songList === []): ?>
                    <p class="admin-muted">Поки немає підтверджених відповідей з обраними піснями.</p>
                <?php else: ?>

                        <?php foreach ($songList as $song): ?>
                        <span><?= htmlspecialchars($song['name'], ENT_QUOTES, 'UTF-8') ?></span>
                            <br>
                        <?php endforeach; ?>

                <?php endif; ?>
        </section>

    </main>
</body>

</html>
