<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Method Not Allowed';
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id > 0) {
    $guest = R::load('guests', $id);

    if ($guest->id) {
        R::trashAll(R::findAll('invite_logs', 'guest_id = ?', [$id]));
        R::trash($guest);
    }
}

header('Location: guests.php');
exit;
