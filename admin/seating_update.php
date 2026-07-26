<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

const MAX_TABLE_NUMBER = 20;

function wantsJsonResponse(): bool
{
    $accept = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));
    $requestedWith = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));

    return str_contains($accept, 'application/json') || $requestedWith === 'xmlhttprequest';
}

function respond(bool $success, string $message, int $statusCode = 200, array $data = []): never
{
    if (wantsJsonResponse()) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            array_merge(['success' => $success, 'message' => $message], $data),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    }

    $_SESSION[$success ? 'admin_flash' : 'admin_flash_error'] = $message;
    header('Location: seating.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Метод запиту не підтримується.', 405);
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    respond(false, 'Сесію форми завершено. Оновіть сторінку і спробуйте ще раз.', 419);
}

$guestId = (int)($_POST['id'] ?? 0);
$tableNumber = trim((string)($_POST['table_number'] ?? ''));

if ($guestId < 1) {
    respond(false, 'Не вказано гостя.', 422);
}

if ($tableNumber !== '') {
    if (!ctype_digit($tableNumber)) {
        respond(false, 'Номер столу має бути числом.', 422);
    }

    $numericTableNumber = (int)$tableNumber;

    if ($numericTableNumber < 1 || $numericTableNumber > MAX_TABLE_NUMBER) {
        respond(false, 'Оберіть стіл від 1 до ' . MAX_TABLE_NUMBER . '.', 422);
    }

    $tableNumber = (string)$numericTableNumber;
}

$guest = R::load('guests', $guestId);

if (!$guest->id) {
    respond(false, 'Гостя не знайдено.', 404);
}

if ((string)$guest->status !== 'confirmed') {
    respond(false, 'Розсаджувати можна лише підтверджених гостей.', 422);
}

$hasAttendanceBreakdown = $guest->primary_attends !== null || $guest->partner_attends !== null;
$primaryAttends = $hasAttendanceBreakdown ? (int)$guest->primary_attends : 1;
$partnerAttends = $hasAttendanceBreakdown ? (int)$guest->partner_attends : (int)$guest->plus_one;

if ($primaryAttends + $partnerAttends < 1) {
    respond(false, 'У цьому запрошенні немає гостей, які підтвердили присутність.', 422);
}

$guest->table_number = $tableNumber === '' ? null : $tableNumber;
$guest->updated_at = date('Y-m-d H:i:s');
R::store($guest);
logInviteAction($guestId, 'seating_updated');

respond(
    true,
    $tableNumber === '' ? 'Гостя переміщено до списку без столу.' : 'Гостя переміщено за стіл ' . $tableNumber . '.',
    200,
    ['table_number' => $tableNumber]
);
