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
    ['title' => 'Далекий 2016', 'text' => 'Наша історія почалася 10 років тому. Тоді ми ще не знали, що попереду на нас чекають тисячі спільних моментів, сотні мрій і одне велике кохання на двох.'],
    ['title' => 'Ближче одне до одного', 'text' => 'Усе починалося з простих розмов, щирих посмішок і бажання бути поруч. День за днем ми знайомилися одне з одним і поступово зрозуміли, що знайшли щось особливе.'],
    ['title' => 'Наші маршрути', 'text' => 'Подорожі, прогулянки і випадкові кадри склалися в одну дорогу, якою вже хочеться йти разом.'],
    ['title' => 'Відстань не стала на заваді', 'text' => 'Наші почуття пройшли перевірку відстанню. Три роки ми жили в різних містах, рахували дні до зустрічей і щоразу переконувалися: справжнє кохання не вимірюється кілометрами.'],
    ['title' => 'Рішення', 'text' => 'Одного дня стало зрозуміло: це вже не просто спільний шлях, а бажання будувати майбутнє як сімʼя.'],
    ['title' => 'Наші мрії стали реальністю', 'text' => 'Разом ми будували не лише стосунки, а й дім. Цеглинка за цеглинкою, мрія за мрією — так з’явилося місце, яке ми називаємо своїм.'],
    ['title' => 'Коріння нашої історії', 'text' => 'Кажуть, для щастя потрібно посадити дерево. Ми посадили свої дерева і тепер із радістю спостерігаємо, як вони ростуть разом із нашою сім’єю.'],
    ['title' => 'Наша пухнаста сім’я', 'text' => 'Наш дім став ще затишнішим, коли в ньому з’явилися два котики. Вони щодня нагадують нам, що щастя складається з маленьких моментів, тепла та любові.'],
    ['title' => 'Продовження історії', 'text' => 'За ці 10 років ми вже побудували дім, посадили дерева і створили свою маленьку сім’ю. 01.08 ми робимо ще один важливий крок — стаємо чоловіком і дружиною. І дуже раді, що саме ви розділяєте цей день разом із нами.'],
    ['title' => 'Далі разом', 'text' => 'А далі — це вже наша спільна дорога, на якій ми хочемо бути поруч, розділяючи радість, підтримку і любов у кожному кроці.'],
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

<body class="about-page2 about-orbit-page">
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
            <a class="section-action about-back-link is-visible" href="<?= e($ticketUrl) ?>" data-about-back>Повернутись</a>
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
            let previousIndex = 0;

            const syncMobileSceneHeight = () => {
                const activeSlide = slides[index];

                if (activeSlide && window.matchMedia('(max-width: 820px)').matches) {
                    activeSlide.parentElement.style.minHeight = `${activeSlide.offsetHeight}px`;
                } else if (activeSlide) {
                    activeSlide.parentElement.style.minHeight = '';
                }
            };

            const render = (direction = 1) => {
                const directionClass = direction > 0 ? 'is-orbit-next' : 'is-orbit-prev';
                document.body.classList.toggle('is-about-next', direction > 0);
                document.body.classList.toggle('is-about-prev', direction < 0);

                slides.forEach((slide, slideIndex) => {
                    slide.classList.remove('is-exiting-left', 'is-exiting-right', 'is-orbit-next', 'is-orbit-prev');
                    slide.classList.toggle('is-active', slideIndex === index);

                    if (slideIndex === previousIndex && previousIndex !== index) {
                        slide.classList.add(direction > 0 ? 'is-exiting-left' : 'is-exiting-right');
                    } else if (slideIndex !== index) {
                        slide.classList.add(directionClass);
                    }
                });

                back?.classList.toggle('is-visible', index === 0 || index === slides.length - 1);
                if (count) {
                    count.textContent = `${index + 1} / ${slides.length}`;
                }

                requestAnimationFrame(syncMobileSceneHeight);
            };

            prev?.addEventListener('click', () => {
                previousIndex = index;
                index = (index - 1 + slides.length) % slides.length;
                render(-1);
            });

            next?.addEventListener('click', () => {
                previousIndex = index;
                index = (index + 1) % slides.length;
                render(1);
            });

            render();
            window.addEventListener('resize', syncMobileSceneHeight);
        })();
    </script>
</body>

</html>