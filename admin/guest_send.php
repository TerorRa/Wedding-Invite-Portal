<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $guest = R::load('guests', $id);

    if ($guest->id) {
        $guest->is_send = 1;
        R::store($guest);
    }
}

header('Location: guests.php');
exit;
