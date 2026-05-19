<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$errors = [];
$editingId = (int)($_GET['edit'] ?? $_POST['id'] ?? 0);
$editingItem = $editingId > 0 ? R::load('dayprograms', $editingId) : null;

if ($editingItem !== null && !$editingItem->id) {
    $editingItem = null;
    $editingId = 0;
}

$form = [
    'event_time' => $editingItem !== null ? (string)$editingItem->event_time : '',
    'title' => $editingItem !== null ? (string)$editingItem->title : '',
    'description' => $editingItem !== null ? (string)$editingItem->description : '',
    'sort_order' => $editingItem !== null ? (string)(int)$editingItem->sort_order : '',
    'is_active' => $editingItem !== null ? (string)(int)$editingItem->is_active : '1',
];

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function emptyToNull(string $value): ?string
{
    return $value === '' ? null : $value;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? 'save');
    $id = (int)($_POST['id'] ?? 0);

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Сесію форми завершено. Оновіть сторінку і спробуйте ще раз.';
    }

    if ($action === 'delete') {
        if ($errors === []) {
            $item = $id > 0 ? R::load('dayprograms', $id) : null;

            if ($item !== null && $item->id) {
                R::trash($item);
            }

            header('Location: program.php');
            exit;
        }
    } else {
        foreach ($form as $key => $value) {
            $form[$key] = trim((string)($_POST[$key] ?? ''));
        }

        $form['is_active'] = isset($_POST['is_active']) ? '1' : '0';

        if ($form['event_time'] === '') {
            $errors[] = 'Вкажіть час пункту програми.';
        }

        if ($form['title'] === '') {
            $errors[] = 'Вкажіть назву пункту програми.';
        }

        if ($form['sort_order'] === '' || !ctype_digit($form['sort_order'])) {
            $errors[] = 'Порядок має бути цілим числом.';
        } elseif (R::count('dayprograms', 'sort_order = ? AND id <> ?', [(int)$form['sort_order'], $id]) > 0) {
            $errors[] = 'Такий порядок уже використовується іншим пунктом.';
        }

        if ($errors === []) {
            $item = $id > 0 ? R::load('dayprograms', $id) : R::dispense('dayprograms');

            if ($id > 0 && !$item->id) {
                $errors[] = 'Пункт програми не знайдено.';
            } else {
                $item->event_time = $form['event_time'];
                $item->title = $form['title'];
                $item->description = emptyToNull($form['description']);
                $item->sort_order = (int)$form['sort_order'];
                $item->is_active = (int)$form['is_active'];

                R::store($item);

                header('Location: program.php');
                exit;
            }
        }
    }
}

$programItems = R::findAll('dayprograms', 'ORDER BY sort_order ASC, id ASC');
?>
<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Програма дня</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <main class="admin-shell">
        <nav class="admin-nav" aria-label="Адмін-меню">
            <a href="dashboard.php">Dashboard</a>
            <a href="guests.php">Гості</a>
            <a class="is-active" href="program.php">Програма</a>
            <a href="import.php">Імпорт</a>
            <a href="export.php">Експорт</a>
            <a href="logout.php">Вийти</a>
        </nav>

        <div class="admin-header">
            <div>
                <p class="admin-eyebrow">Wedding Invite Portal</p>
                <h1><?= $editingItem !== null ? 'Редагування програми' : 'Програма дня' ?></h1>
            </div>
            <?php if ($editingItem !== null): ?>
                <a class="admin-button admin-button-light" href="program.php">Новий пункт</a>
            <?php endif; ?>
        </div>

        <?php if ($errors !== []): ?>
            <div class="admin-alert">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="admin-form admin-form-wide program-form" action="program.php" method="post">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= $editingItem !== null ? (int)$editingItem->id : 0 ?>">

            <label>
                Час
                <input type="text" name="event_time" value="<?= e($form['event_time']) ?>" placeholder="15:00" required>
            </label>

            <label>
                Порядок
                <input type="number" name="sort_order" value="<?= e($form['sort_order']) ?>" min="0" step="1" placeholder="10" required>
            </label>

            <label class="admin-full">
                Назва
                <input type="text" name="title" value="<?= e($form['title']) ?>" required>
            </label>

            <label class="admin-full">
                Опис
                <textarea name="description" rows="3"><?= e($form['description']) ?></textarea>
            </label>

            <label class="admin-checkbox">
                <input type="checkbox" name="is_active" value="1" <?= $form['is_active'] === '1' ? 'checked' : '' ?>>
                Показувати на сторінці запрошення
            </label>

            <div class="admin-form-actions">
                <button type="submit"><?= $editingItem !== null ? 'Зберегти зміни' : 'Додати пункт' ?></button>
                <?php if ($editingItem !== null): ?>
                    <a href="program.php">Скасувати</a>
                <?php endif; ?>
            </div>
        </form>

        <section class="admin-table-panel program-table-panel">
            <div class="admin-table-wrap">
                <table class="admin-table program-table">
                    <thead>
                        <tr>
                            <th>Порядок</th>
                            <th>Час</th>
                            <th>Назва</th>
                            <th>Опис</th>
                            <th>Статус</th>
                            <th>Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($programItems === []): ?>
                            <tr>
                                <td colspan="6" class="admin-empty">Пункти програми ще не додані.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($programItems as $item): ?>
                            <tr>
                                <td><?= (int)$item->sort_order ?></td>
                                <td><?= e($item->event_time) ?></td>
                                <td><?= e($item->title) ?></td>
                                <td><?= e($item->description) ?></td>
                                <td>
                                    <span class="status-pill"><?= (int)$item->is_active === 1 ? 'Активний' : 'Прихований' ?></span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="program.php?edit=<?= (int)$item->id ?>">Редагувати</a>
                                        <form action="program.php" method="post" onsubmit="return confirm('Видалити пункт програми?');">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$item->id ?>">
                                            <button type="submit">Видалити</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
