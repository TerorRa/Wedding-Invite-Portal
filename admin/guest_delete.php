<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Method Not Allowed';
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Invalid request';
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id > 0) {
    $guest = R::load('guests', $id);

    if ($guest->id) {
        R::trashAll(R::findAll('invitelogs', 'guest_id = ?', [$id]));
        R::trash($guest);
    }
}

header('Location: guests.php');
exit;
