<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$telegramConfig = is_file(__DIR__ . '/config/telegram.php') ? require __DIR__ . '/config/telegram.php' : [];
$botToken = trim((string)($telegramConfig['bot_token'] ?? ''));

function tgJsonResponse(array $data = ['ok' => true], int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function tgApi(string $method, array $payload = []): ?array
{
    global $botToken;

    if ($botToken === '') {
        return null;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'timeout' => 12,
            'ignore_errors' => true,
        ],
    ]);

    $response = @file_get_contents('https://api.telegram.org/bot' . $botToken . '/' . $method, false, $context);

    if (!is_string($response) || $response === '') {
        return null;
    }

    $decoded = json_decode($response, true);

    return is_array($decoded) ? $decoded : null;
}

function tgSendMessage(int|string $chatId, string $text, ?array $keyboard = null): void
{
    $payload = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true,
    ];

    if ($keyboard !== null) {
        $payload['reply_markup'] = $keyboard;
    }

    tgApi('sendMessage', $payload);
}

function tgAnswerCallback(string $callbackId, string $text = ''): void
{
    tgApi('answerCallbackQuery', [
        'callback_query_id' => $callbackId,
        'text' => $text,
    ]);
}

function tgMainKeyboard(string $inviteUrl): array
{
    return [
        'inline_keyboard' => [
            [
                ['text' => 'Залишити коментар організатору', 'callback_data' => 'leave_comment'],
            ],
            [
                ['text' => 'Надіслати фото або відео', 'callback_data' => 'upload_media'],
            ],
            [
                ['text' => 'Моє запрошення', 'url' => $inviteUrl],
                ['text' => 'Дата і локація', 'callback_data' => 'event_info'],
            ],
        ],
    ];
}

function tgInviteUrl(string $inviteCode): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = rtrim(str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/telegram_bot.php'))), '/');
    $projectPath = $scriptDir === '/' ? '' : $scriptDir;

    return $scheme . '://' . $host . $projectPath . '/invite.php?code=' . rawurlencode($inviteCode);
}

function tgGuestByChat(int|string $chatId): ?RedBeanPHP\OODBBean
{
    return R::findOne('guests', 'telegram_chat_id = ?', [(string)$chatId]);
}

function tgGuestByInviteCode(string $inviteCode): ?RedBeanPHP\OODBBean
{
    return R::findOne('guests', 'invite_code = ?', [$inviteCode]);
}

function tgBindGuest(RedBeanPHP\OODBBean $guest, array $from, int|string $chatId): void
{
    $telegramId = (string)($from['id'] ?? '');
    $username = trim((string)($from['username'] ?? ''));

    $guest->telegram_id = $telegramId !== '' ? $telegramId : null;
    $guest->telegram_chat_id = (string)$chatId;
    $guest->telegram_username = $username !== '' ? $username : null;
    $guest->telegram = $username !== '' ? '@' . $username : ($telegramId !== '' ? 'tg:' . $telegramId : $guest->telegram);

    if (empty($guest->telegram_connected_at)) {
        $guest->telegram_connected_at = date('Y-m-d H:i:s');
    }

    R::store($guest);
    logInviteAction((int)$guest->id, 'telegram_connected');
}

function tgExtractStartCode(string $text): string
{
    $text = trim($text);

    if (!str_starts_with($text, '/start')) {
        return '';
    }

    $parts = preg_split('/\s+/', $text, 2);

    return isset($parts[1]) ? trim((string)$parts[1]) : '';
}

function tgAppendWish(RedBeanPHP\OODBBean $guest, string $message, array $from): void
{
    $name = trim((string)(($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? '')));
    $username = trim((string)($from['username'] ?? ''));
    $author = $username !== '' ? '@' . $username : ($name !== '' ? $name : 'Telegram');
    $entry = '[' . date('Y-m-d H:i') . '] ' . $author . ': ' . $message;

    $existing = trim((string)$guest->wish);
    $guest->wish = $existing === '' ? $entry : $existing . "\n\n" . $entry;
    $guest->telegram_state = null;

    R::store($guest);
    logInviteAction((int)$guest->id, 'telegram_wish_saved');
}

function tgSafeFileName(string $value): string
{
    $value = preg_replace('/[^a-zA-Z0-9._-]+/', '_', $value) ?: 'file';

    return trim($value, '._-') ?: 'file';
}

function tgExtensionFromMime(?string $mimeType, string $fallback = 'bin'): string
{
    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'video/mp4' => 'mp4',
        'video/quicktime' => 'mov',
        'video/webm' => 'webm',
    ];

    return $map[(string)$mimeType] ?? $fallback;
}

function tgStoreMedia(RedBeanPHP\OODBBean $guest, array $fileData, string $type, ?string $caption = null): bool
{
    global $botToken;

    $fileId = (string)($fileData['file_id'] ?? '');

    if ($botToken === '' || $fileId === '') {
        return false;
    }

    $fileInfo = tgApi('getFile', ['file_id' => $fileId]);
    $filePath = (string)($fileInfo['result']['file_path'] ?? '');

    if ($filePath === '') {
        return false;
    }

    $mimeType = (string)($fileData['mime_type'] ?? '');
    $originalName = (string)($fileData['file_name'] ?? basename($filePath));
    $extension = pathinfo($originalName, PATHINFO_EXTENSION) ?: tgExtensionFromMime($mimeType, pathinfo($filePath, PATHINFO_EXTENSION) ?: 'bin');
    $directory = __DIR__ . '/input/guest_' . (int)$guest->id;

    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        return false;
    }

    $storedName = date('Ymd_His') . '_' . tgSafeFileName($type) . '_' . tgSafeFileName($fileId) . '.' . tgSafeFileName($extension);
    $absolutePath = $directory . '/' . $storedName;
    $downloadUrl = 'https://api.telegram.org/file/bot' . $botToken . '/' . $filePath;

    if (!@copy($downloadUrl, $absolutePath)) {
        return false;
    }

    $relativePath = 'input/guest_' . (int)$guest->id . '/' . $storedName;
    $upload = R::dispense('guestuploads');
    $upload->guest_id = (int)$guest->id;
    $upload->telegram_file_id = $fileId;
    $upload->telegram_file_unique_id = $fileData['file_unique_id'] ?? null;
    $upload->file_type = $type;
    $upload->original_name = $originalName !== '' ? $originalName : null;
    $upload->stored_path = $relativePath;
    $upload->mime_type = $mimeType !== '' ? $mimeType : null;
    $upload->file_size = isset($fileData['file_size']) ? (int)$fileData['file_size'] : null;
    $upload->caption = $caption;

    R::store($upload);
    logInviteAction((int)$guest->id, 'telegram_media_uploaded');

    return true;
}

if ($botToken === '') {
    tgJsonResponse(['ok' => false, 'error' => 'TELEGRAM_BOT_TOKEN is not configured'], 500);
}

$rawBody = file_get_contents('php://input');
$update = json_decode(is_string($rawBody) ? $rawBody : '', true);

if (!is_array($update)) {
    tgJsonResponse();
}

if (isset($update['callback_query'])) {
    $callback = $update['callback_query'];
    $data = (string)($callback['data'] ?? '');
    $chatId = $callback['message']['chat']['id'] ?? null;
    $guest = $chatId !== null ? tgGuestByChat($chatId) : null;

    tgAnswerCallback((string)$callback['id']);

    if ($chatId === null || $guest === null) {
        tgJsonResponse();
    }

    if ($data === 'leave_comment') {
        $guest->telegram_state = 'awaiting_comment';
        R::store($guest);
        tgSendMessage($chatId, 'Напишіть одним повідомленням коментар або побажання для організаторів. Я збережу його у вашій картці гостя.');
    } elseif ($data === 'upload_media') {
        tgSendMessage($chatId, 'Надішліть сюди фото або відео з весілля. Я збережу файл для організаторів у галереї спогадів.');
    } elseif ($data === 'event_info') {
        tgSendMessage($chatId, "Весілля Ростислава та Катерини\nДата: 01.08.2026\nЛокація: Петрівський Бровар\nКарти: https://maps.app.goo.gl/W17bXceU78X7ecWGA", tgMainKeyboard(tgInviteUrl((string)$guest->invite_code)));
    }

    tgJsonResponse();
}

$message = $update['message'] ?? null;

if (!is_array($message)) {
    tgJsonResponse();
}

$chatId = $message['chat']['id'] ?? null;
$from = is_array($message['from'] ?? null) ? $message['from'] : [];
$text = trim((string)($message['text'] ?? ''));

if ($chatId === null) {
    tgJsonResponse();
}

$startCode = tgExtractStartCode($text);
$guest = $startCode !== '' ? tgGuestByInviteCode($startCode) : tgGuestByChat($chatId);

if ($guest === null) {
    tgSendMessage($chatId, 'Я не знайшов ваше запрошення. Відкрийте бота через кнопку в персональному запрошенні.');
    tgJsonResponse();
}

tgBindGuest($guest, $from, $chatId);

if ($startCode !== '') {
    tgSendMessage(
        $chatId,
        'Вітаю, ' . htmlspecialchars((string)$guest->name, ENT_QUOTES, 'UTF-8') . "! Я весільний бот-помічник. Тут можна залишити коментар організаторам, надіслати фото або швидко повернутися до запрошення.",
        tgMainKeyboard(tgInviteUrl((string)$guest->invite_code))
    );
    tgJsonResponse();
}

if ($text !== '' && (string)$guest->telegram_state === 'awaiting_comment') {
    tgAppendWish($guest, $text, $from);
    tgSendMessage($chatId, 'Дякуємо. Коментар збережено для організаторів.', tgMainKeyboard(tgInviteUrl((string)$guest->invite_code)));
    tgJsonResponse();
}

if ($text === '/menu' || $text === '/start') {
    tgSendMessage($chatId, 'Оберіть дію:', tgMainKeyboard(tgInviteUrl((string)$guest->invite_code)));
    tgJsonResponse();
}

$caption = isset($message['caption']) ? trim((string)$message['caption']) : null;
$stored = false;

if (!empty($message['photo']) && is_array($message['photo'])) {
    $photo = end($message['photo']);
    $stored = is_array($photo) && tgStoreMedia($guest, $photo, 'photo', $caption);
} elseif (!empty($message['video']) && is_array($message['video'])) {
    $stored = tgStoreMedia($guest, $message['video'], 'video', $caption);
} elseif (!empty($message['document']) && is_array($message['document'])) {
    $mimeType = (string)($message['document']['mime_type'] ?? '');

    if (str_starts_with($mimeType, 'image/') || str_starts_with($mimeType, 'video/')) {
        $stored = tgStoreMedia($guest, $message['document'], str_starts_with($mimeType, 'image/') ? 'photo' : 'video', $caption);
    }
}

if ($stored) {
    tgSendMessage($chatId, 'Файл збережено. Дякуємо, що ділитеся спогадами.', tgMainKeyboard(tgInviteUrl((string)$guest->invite_code)));
} elseif ($text !== '') {
    tgSendMessage($chatId, 'Я можу зберегти коментар після кнопки “Залишити коментар організатору” або прийняти фото/відео. Оберіть дію:', tgMainKeyboard(tgInviteUrl((string)$guest->invite_code)));
}

tgJsonResponse();
