<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function renderError(string $message, ?string $inviteCode = null): void
{
    $_SESSION['rsvp_error'] = $message;

    if ($inviteCode !== null && $inviteCode !== '') {
        header('Location: invite.php?code=' . urlencode($inviteCode) . '&error=1');
    } else {
        header('Location: index.php?error=1');
    }

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    renderError('Форма має бути відправлена методом POST.');
}

$inviteCode = trim((string)($_POST['invite_code'] ?? ''));
$willAttendRaw = $_POST['will_attend'] ?? null;
$plusOne = (int)($_POST['plus_one'] ?? 0);
$plusOneName = trim((string)($_POST['plus_one_name'] ?? ''));
$partnerDrink = trim((string)($_POST['partner_drink'] ?? ''));

if ($inviteCode === '') {
    renderError('Код запрошення обовʼязковий.', $inviteCode);
}

if ($willAttendRaw === null || !in_array((string)$willAttendRaw, ['0', '1'], true)) {
    renderError('Оберіть, чи будете ви присутні.', $inviteCode);
}

$willAttend = (int)$willAttendRaw;
$plusOne = $plusOne === 1 ? 1 : 0;

if ($plusOne === 1 && $plusOneName === '') {
    renderError('Будь ласка, вкажіть імʼя супутника.', $inviteCode);
}
if ($plusOne === 1 && $partnerDrink === '') {
    renderError('Будь ласка, оберіть напій для супутника.', $inviteCode);
}

$guest = R::findOne('guests', 'invite_code = ?', [$inviteCode]);

if ($guest === null) {
    renderError('Запрошення не знайдено.', $inviteCode);
}

$rsvpDeadline = new DateTimeImmutable('2026-07-02 23:59:59', new DateTimeZone('Europe/Kiev'));
$hasGuestAnswered = trim((string)$guest->answered_at) !== ''
    || in_array((string)$guest->status, ['confirmed', 'declined'], true)
    || trim((string)$guest->will_attend) !== '';

if ((new DateTimeImmutable('now', new DateTimeZone('Europe/Kiev'))) > $rsvpDeadline && !$hasGuestAnswered) {
    renderError('Ми чекали на вашу відповідь, але, на жаль, не отримали її вчасно. Звʼяжіться з нами, якщо ви все ж бажаєте бути присутніми на нашому святі.', $inviteCode);
}

$invitationType = (string)($guest->invitation_type ?: ((int)$guest->max_plus_one === 1 ? 'single_plus_one' : 'single'));
$isCoupleInvite = $invitationType === 'couple';

if ($willAttend === 0) {
    $plusOne = 0;
    $plusOneName = '';
    $partnerDrink = '';
} elseif ($isCoupleInvite) {
    $plusOne = 1;
    $plusOneName = trim((string)$guest->plus_one_name);
} elseif ($plusOne === 1 && (int)$guest->max_plus_one !== 1) {
    $plusOne = 0;
    $plusOneName = '';
    $partnerDrink = '';
}

if ($willAttend === 1 && $plusOne === 1 && $plusOneName === '') {
    renderError('Будь ласка, вкажіть імʼя супутника або партнера.', $inviteCode);
}

$guest->will_attend = $willAttend;
$guest->plus_one = $plusOne;
$guest->plus_one_name = $plusOne === 1 ? $plusOneName : null;
$guest->drink = trim((string)($_POST['drink'] ?? ''));
$guest->partner_drink = $plusOne === 1 ? $partnerDrink : null;
$guest->food_notes = trim((string)($_POST['food_notes'] ?? ''));
$guest->need_transfer = isset($_POST['need_transfer']) ? 1 : 0;
$guest->prepare_toast = $willAttend === 1 && (string)($_POST['prepare_toast'] ?? '0') === '1' ? 1 : 0;
$guest->song_request = trim((string)($_POST['song_request'] ?? ''));
$guest->wish = trim((string)($_POST['wish'] ?? ''));
$guest->answered_at = date('Y-m-d H:i:s');
$guest->status = $willAttend === 1 ? 'confirmed' : 'declined';

R::store($guest);
logInviteAction((int)$guest->id, 'submitted_rsvp');

header('Location: ticket.php?code=' . urlencode($inviteCode));
exit;
