<?php

declare(strict_types=1);

require_once __DIR__ . '/libs/rb.php';

$dbConfig = require __DIR__ . '/config/db.php';

$charset = $dbConfig['charset'] ?? 'utf8mb4';
$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    $dbConfig['host'],
    $dbConfig['database'],
    $charset
);

R::setup($dsn, $dbConfig['user'], $dbConfig['password']);
R::exec('SET NAMES ' . $charset);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function generateInviteCode(): string
{
    return bin2hex(random_bytes(8));
}

function logInviteAction(int $guest_id, string $action): void
{
    $log = R::dispense("invitelogs");
    $log->guest_id = $guest_id;
    $log->action = $action;
    $log->ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $log->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    R::store($log);
}
