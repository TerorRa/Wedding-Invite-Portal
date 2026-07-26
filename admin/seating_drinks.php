<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

$tableDrinkCounts = [];

for ($tableNumber = 1; $tableNumber <= 7; $tableNumber++) {
    $tableDrinkCounts[(string)$tableNumber] = [];
}

$guests = R::findAll(
    'guests',
    'status = ? AND table_number IS NOT NULL AND table_number <> ? ORDER BY table_number + 0 ASC, id ASC',
    ['confirmed', '']
);

foreach ($guests as $guest) {
    $tableNumber = trim((string)$guest->table_number);

    if (!isset($tableDrinkCounts[$tableNumber])) {
        continue;
    }

    $hasAttendanceBreakdown = $guest->primary_attends !== null || $guest->partner_attends !== null;
    $primaryAttends = $hasAttendanceBreakdown ? (int)$guest->primary_attends : 1;
    $partnerAttends = $hasAttendanceBreakdown ? (int)$guest->partner_attends : (int)$guest->plus_one;
    $primaryDrink = trim((string)$guest->drink);
    $partnerDrink = trim((string)$guest->partner_drink);

    if ($primaryAttends === 1 && $primaryDrink !== '') {
        $tableDrinkCounts[$tableNumber][$primaryDrink] = ($tableDrinkCounts[$tableNumber][$primaryDrink] ?? 0) + 1;
    }

    if ($partnerAttends === 1 && $partnerDrink !== '') {
        $tableDrinkCounts[$tableNumber][$partnerDrink] = ($tableDrinkCounts[$tableNumber][$partnerDrink] ?? 0) + 1;
    }
}

foreach ($tableDrinkCounts as &$drinkCounts) {
    arsort($drinkCounts, SORT_NUMERIC);
}
unset($drinkCounts);

echo json_encode(
    [
        'success' => true,
        'tables' => $tableDrinkCounts,
    ],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
