<?php

declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

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

function generateTicketNumber(?string $seed = null): string
{
    $source = $seed ?: bin2hex(random_bytes(8));

    return 'WI-0801-' . strtoupper(substr(hash('crc32b', $source), 0, 8));
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') .
        '">';
}

function verifyCsrfToken(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
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
