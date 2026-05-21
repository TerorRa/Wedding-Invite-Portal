<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$fields = [
    'ticket_number',
    'name',
    'personal_greeting',
    'phone',
    'email',
    'telegram',
    'telegram_id',
    'telegram_chat_id',
    'telegram_username',
    'telegram_connected_at',
    'guest_group',
    'invitation_type',
    'status',
    'will_attend',
    'plus_one',
    'plus_one_name',
    'drink',
    'partner_drink',
    'food_notes',
    'need_transfer',
    'prepare_toast',
    'song_request',
    'wish',
    'table_number',
    'opened_at',
    'answered_at',
];

$guests = R::findAll('guests', 'ORDER BY id ASC');
$filename = 'wedding-guests-' . date('Y-m-d-H-i') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

if ($output === false) {
    exit;
}

fputcsv($output, $fields, ';');

foreach ($guests as $guest) {
    $row = [];

    foreach ($fields as $field) {
        $row[] = (string)($guest->$field ?? '');
    }

    fputcsv($output, $row, ';');
}

fclose($output);
exit;
