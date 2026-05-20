<!doctype html>
<html lang="uk">

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
    <style>
        :root {
            --night: #1b2845;
            --sky: #a8bdd8;
            --sky-soft: #c7d6e8;
            --cream: #eef3f6;
            --text: #253d58;
            --muted: #5a7090;
            --gold: #c9a85d;
            --line: rgba(116, 142, 170, 0.34);
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            color: var(--text);
            font-family: Montserrat, Arial, sans-serif;
            background:
                radial-gradient(circle at 18% 10%, rgba(255, 255, 255, 0.58), transparent 28%),
                radial-gradient(circle at 80% 78%, rgba(201, 168, 93, 0.16), transparent 30%),
                linear-gradient(180deg, #91abc8 0%, #b7c9dc 42%, #d8e4ef 100%);
            overflow: hidden;
        }

        .stars,
        .stars::before,
        .stars::after {
            position: fixed;
            inset: 0;
            pointer-events: none;
            content: "";
            background-image:
                radial-gradient(circle, rgba(255, 255, 255, 0.85) 0 1px, transparent 1.8px),
                radial-gradient(circle, rgba(255, 255, 255, 0.5) 0 1px, transparent 2px);
            background-position: 12% 18%, 68% 35%;
            background-size: 130px 130px, 190px 190px;
            animation: drift 18s ease-in-out infinite alternate;
        }

        .stars::before {
            opacity: 0.45;
            transform: rotate(12deg);
            animation-duration: 24s;
        }

        .stars::after {
            opacity: 0.32;
            transform: rotate(-8deg);
            animation-duration: 30s;
        }

        .card {
            position: relative;
            z-index: 1;
            width: min(680px, 100%);
            padding: clamp(30px, 7vw, 54px);
            text-align: center;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.74);
            border-radius: 28px;
            box-shadow: 0 28px 80px rgba(27, 40, 69, 0.18);
            backdrop-filter: blur(16px);
        }

        .script {
            margin: 0 0 8px;
            color: var(--gold);
            font-family: "Great Vibes", cursive;
            font-size: clamp(34px, 8vw, 54px);
            line-height: 1;
        }

        h1 {
            margin: 0 0 18px;
            font-family: "Cormorant Garamond", Georgia, serif;
            font-size: clamp(36px, 7vw, 62px);
            font-weight: 500;
            line-height: 1.02;
        }

        p {
            width: min(520px, 100%);
            margin: 0 auto 24px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.8;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
        }

        a {
            min-height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 22px;
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            background: linear-gradient(135deg, var(--gold), var(--sky));
            border-radius: 999px;
            box-shadow: 0 12px 28px rgba(37, 61, 88, 0.16);
        }

        .secondary {
            color: var(--text);
            background: rgba(255, 255, 255, 0.42);
            border: 1px solid var(--line);
        }

        @keyframes drift {
            from {
                transform: translate3d(0, 0, 0);
            }

            to {
                transform: translate3d(18px, -22px, 0);
            }
        }

        @media (max-width: 560px) {
            body {
                padding: 16px;
            }

            .actions,
            a {
                width: 100%;
            }
        }
    </style>
    <script>
        const params = new URLSearchParams(window.location.search);
        const code = params.get('code');

        if (code) {
            window.location.replace(`invite.php?code=${encodeURIComponent(code)}`);
        }
    </script>
</head>

<body>
    <div class="stars" aria-hidden="true"></div>
    <main class="card">
        <p class="script">Ростислав & Катерина</p>
        <h1>Wedding Invite Portal</h1>
        <p>Персональні запрошення, RSVP та весільні QR-квитки. Для відкриття запрошення використовуйте персональне посилання з кодом гостя.</p>
        <div class="actions">
            <a href="index.php">Відкрити портал</a>
            <a class="secondary" href="admin/login.php">Адмін-панель</a>
        </div>
    </main>
</body>

</html>
