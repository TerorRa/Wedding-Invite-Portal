<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$code = trim((string)($_GET['code'] ?? ''));
$guest = $code !== '' ? R::findOne('guests', 'invite_code = ?', [$code]) : null;

if ($guest !== null) {
    logInviteAction((int)$guest->id, 'viewed_about');
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function assetUrl(string $path): string
{
    $scriptDir = str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '')));
    $basePath = rtrim($scriptDir, '/');

    if ($basePath === '/' || $basePath === '.') {
        $basePath = '';
    }

    return $basePath . '/' . ltrim($path, '/');
}

$styleVersion = (string)(@filemtime(__DIR__ . '/assets/css/style.css') ?: time());
$aboutStyleVersion = (string)(@filemtime(__DIR__ . '/assets/css/style_about.css') ?: time());
$aboutPhotos = [];
$aboutPhotoDirectory = __DIR__ . '/assets/about';

if (is_dir($aboutPhotoDirectory)) {
    $files = glob($aboutPhotoDirectory . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [];
    sort($files, SORT_NATURAL);

    foreach ($files as $file) {
        if (is_file($file)) {
            $aboutPhotos[] = 'assets/about/' . basename($file);
        }
    }
}

$ticketUrl = 'ticket.php' . ($code !== '' ? '?code=' . urlencode($code) : '');
$inviteUrl = 'invite.php' . ($code !== '' ? '?code=' . urlencode($code) : '');
$fallbackPhoto = is_file(__DIR__ . '/assets/about/main/0.jpg') ? 'assets/about/main/0.jpg' : 'assets/img/hero-new.webp';

$chapters = [
    ['title' => 'Далекий 2016', 'text' => 'Так почалася наша історія: з простих зустрічей, усмішок і моментів, які поступово стали дуже важливими.'],
    ['title' => 'Ближче одне до одного', 'text' => 'Ми вчилися бути поруч у свята і в будні, чути одне одного і знаходити тепло навіть у дрібницях.'],
    ['title' => 'Наші маршрути', 'text' => 'Подорожі, прогулянки і випадкові кадри склалися в одну дорогу, якою вже хочеться йти разом.'],
    ['title' => 'Теплі традиції', 'text' => 'У нас зʼявилися свої жарти, місця, вечори й маленькі ритуали, з яких народжується відчуття дому.'],
    ['title' => 'Рішення', 'text' => 'Одного дня стало зрозуміло: це вже не просто спільний шлях, а бажання будувати майбутнє як сімʼя.'],
    ['title' => 'Поруч із вами', 'text' => 'У нашій історії є люди, без яких вона була б іншою. Саме тому нам важливо розділити цей день із близькими.'],
    ['title' => 'Перед святом', 'text' => 'Ми зберігаємо хвилювання і передчуття дня, у якому буде багато любові, музики, обіймів і світла.'],
    ['title' => 'Далі разом', 'text' => 'Весілля стане не фіналом історії, а її новою главою. І нам радісно починати її поруч із вами.'],
];

$storyItems = [];
$photosForStory = $aboutPhotos !== [] ? $aboutPhotos : [$fallbackPhoto];

foreach ($chapters as $index => $chapter) {
    $storyItems[] = [
        'photo' => $photosForStory[$index % count($photosForStory)],
        'title' => $chapter['title'],
        'text' => $chapter['text'],
    ];
}
?>
<!doctype html>
<html lang="uk" class="no-js">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Про нас</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,400&family=Great+Vibes&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= e($styleVersion) ?>">
    <link rel="stylesheet" href="assets/css/style_about.css?v=<?= e($aboutStyleVersion) ?>">
</head>

<body class="about-page about-orbit-page">
    <main class="about-orbit" data-about-orbit aria-label="Наша історія">
        <div class="about-orbit__stars" aria-hidden="true"></div>

        <section class="about-orbit__scene" aria-live="polite">
            <?php foreach ($storyItems as $index => $item): ?>
                <article class="about-orbit-slide<?= $index === 0 ? ' is-active' : '' ?>" data-about-slide>
                    <figure class="about-orbit-photo">
                        <img src="<?= e(assetUrl($item['photo'])) ?>" alt="" loading="<?= $index === 0 ? 'eager' : 'lazy' ?>" decoding="async" <?= $index === 0 ? 'fetchpriority="high"' : '' ?>>
                    </figure>
                    <div class="about-orbit-card">
                        <p class="t-scr">Ростислав & Катерина</p>
                        <h1><?= e($item['title']) ?></h1>
                        <p><?= e($item['text']) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <nav class="about-orbit-controls" aria-label="Навігація історією">
            <a class="section-action about-back-link is-visible" href="<?= e($inviteUrl) ?>" data-about-back>Повернутись до запрошення</a>
            <button class="about-nav-btn" type="button" data-about-prev aria-label="Попередня сцена">&lt;</button>
            <span class="about-orbit-count" data-about-count>1 / <?= count($storyItems) ?></span>
            <button class="about-nav-btn" type="button" data-about-next aria-label="Наступна сцена">&gt;</button>
        </nav>
    </main>

    <script>
        (() => {
            const slides = [...document.querySelectorAll('[data-about-slide]')];
            const prev = document.querySelector('[data-about-prev]');
            const next = document.querySelector('[data-about-next]');
            const back = document.querySelector('[data-about-back]');
            const count = document.querySelector('[data-about-count]');
            let index = 0;

            const render = (direction = 1) => {
                slides.forEach((slide, slideIndex) => {
                    slide.classList.toggle('is-active', slideIndex === index);
                    slide.classList.toggle('is-exiting-left', slideIndex !== index && direction > 0);
                    slide.classList.toggle('is-exiting-right', slideIndex !== index && direction < 0);
                });

                back?.classList.toggle('is-visible', index === 0 || index === slides.length - 1);
                if (count) {
                    count.textContent = `${index + 1} / ${slides.length}`;
                }
            };

            prev?.addEventListener('click', () => {
                index = (index - 1 + slides.length) % slides.length;
                render(-1);
            });

            next?.addEventListener('click', () => {
                index = (index + 1) % slides.length;
                render(1);
            });

            render();
        })();
    </script>
</body>

</html>
