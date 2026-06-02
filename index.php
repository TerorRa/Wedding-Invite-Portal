<!doctype html>
<html lang="uk" class="no-js">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wedding Invite Portal</title>
    <meta name="description" content="Персональний портал весільних запрошень Ростислава та Катерини">
    <meta property="og:title" content="Wedding Invite Portal">
    <meta property="og:description" content="Персональні запрошення, RSVP та весільні QR-квитки">
    <meta property="og:type" content="website">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Great+Vibes&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        const params = new URLSearchParams(window.location.search);
        const code = params.get('code');

        if (code) {
            window.location.replace(`invite.php?code=${encodeURIComponent(code)}`);
        }
    </script>
</head>

<body>
    <main class="page-shell pass-shell">
        <div class="cosmic-effects cosmic-effects--pass" aria-hidden="true"></div>
        <section class="welcome reveal pass-declined-card">
            <p class="eyebrow">Ростислав & Катерина</p>
            <h1>Wedding Invite Portal</h1>
            <p>Персональні запрошення, RSVP та весільні QR-квитки. Для відкриття запрошення використовуйте персональне посилання з кодом гостя.</p>
            <div class="pass-actions">
                <a class="section-action pass-about-button" href="about.php">Про нас</a>
                <a class="section-action btn-o" href="admin/login.php">Адмін-панель</a>
            </div>
        </section>
    </main>
    <script src="assets/js/invite.js"></script>
</body>

</html>
