<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function redirectBack(string $message = '', bool $isError = false): void
{
    $redirectTo = (string)($_POST['redirect_to'] ?? 'guests.php');

    if ($redirectTo === '' || strpos($redirectTo, 'guests.php') !== 0) {
        $redirectTo = 'guests.php';
    }

    if ($message !== '') {
        $_SESSION[$isError ? 'admin_flash_error' : 'admin_flash'] = $message;
    }

    header('Location: ' . $redirectTo);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectBack('Форма має бути відправлена методом POST.', true);
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    redirectBack('Сесію форми завершено. Оновіть сторінку і спробуйте ще раз.', true);
}

$id = (int)($_POST['id'] ?? 0);
$drink = trim((string)($_POST['drink'] ?? ''));
$plusOne = isset($_POST['plus_one']) ? 1 : 0;
$plusOneName = trim((string)($_POST['plus_one_name'] ?? ''));
$partnerDrink = trim((string)($_POST['partner_drink'] ?? ''));

if ($id <= 0) {
    redirectBack('Гостя не знайдено.', true);
}

if ($drink === '') {
    redirectBack('Оберіть напій гостя перед підтвердженням.', true);
}

$guest = R::load('guests', $id);

if (!$guest->id) {
    redirectBack('Гостя не знайдено.', true);
}

$invitationType = (string)($guest->invitation_type ?: ((int)$guest->max_plus_one === 1 ? 'single_plus_one' : 'single'));
$allowsPlusOne = $invitationType === 'single_plus_one' || $invitationType === 'couple' || (int)$guest->max_plus_one === 1;

if ($plusOne === 1 && !$allowsPlusOne) {
    redirectBack('Для цього гостя +1 не дозволено.', true);
}

if ($plusOne === 1 && $plusOneName === '') {
    redirectBack('Вкажіть імʼя +1 / партнера.', true);
}

if ($plusOne === 1 && $partnerDrink === '') {
    redirectBack('Оберіть напій для +1 / партнера.', true);
}

if ($invitationType === 'couple' && $plusOneName === '') {
    $plusOneName = trim((string)$guest->plus_one_name);
}

$guest->will_attend = 1;
$guest->status = 'confirmed';
$guest->drink = $drink;
$guest->plus_one = $plusOne;
$guest->plus_one_name = ($invitationType === 'couple' || $plusOne === 1) ? $plusOneName : null;
$guest->partner_drink = $plusOne === 1 ? $partnerDrink : null;
$guest->primary_attends = 1;
$guest->partner_attends = $plusOne;
$guest->answered_at = date('Y-m-d H:i:s');

R::store($guest);
logInviteAction((int)$guest->id, 'admin_confirmed_rsvp');

$confirmedNames = (string)$guest->name;

if ($plusOne === 1 && $plusOneName !== '') {
    $confirmedNames .= ' та ' . $plusOneName;
}

redirectBack('Присутність підтверджено: ' . $confirmedNames . '.');
