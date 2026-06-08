document.documentElement.classList.add('js-ready');
document.documentElement.classList.remove('no-js');

const openInviteButton = document.querySelector('[data-open-invite]');
const inviteContent = document.querySelector('#inviteContent');
const copyLinkButton = document.querySelector('[data-copy-link]');
const musicButton = document.querySelector('[data-music-toggle]');
const bgMusic = document.querySelector('[data-bg-music]');

function installRsvpHighlightStyles() {
    if (document.querySelector('#rsvpHighlightStyles')) {
        return;
    }

    const style = document.createElement('style');
    style.id = 'rsvpHighlightStyles';
    style.textContent = `
        .rsvp-section {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            padding-top: clamp(72px, 9vw, 120px) !important;
            padding-bottom: clamp(76px, 9vw, 130px) !important;
            background:
                radial-gradient(circle at 18% 8%, rgba(234, 217, 165, .28), transparent 25%),
                radial-gradient(circle at 50% 110%, rgba(130, 154, 184, .36), transparent 38%),
                linear-gradient(180deg, #111d31 0%, #283a5a 52%, #6f86a6 100%) !important;
            border-top: 2px solid rgba(234, 217, 165, .68);
            border-bottom: 2px solid rgba(234, 217, 165, .58);
        }

        .rsvp-section::before {
            content: "Важливо: будь ласка, заповніть анкету до 17.07.2026";
            position: relative;
            z-index: 3;
            display: block;
            width: min(760px, calc(100% - 32px));
            margin: 0 auto 22px;
            padding: 13px 18px;
            color: #1f2d44;
            text-align: center;
            font-size: clamp(13px, 2.8vw, 16px);
            font-weight: 800;
            letter-spacing: .07em;
            line-height: 1.35;
            text-transform: uppercase;
            background: linear-gradient(135deg, #fff6db, #ead9a5 48%, #fff8e8 100%);
            border: 1px solid rgba(255, 255, 255, .78);
            border-radius: 999px;
            box-shadow: 0 12px 38px rgba(31, 45, 68, .28), 0 0 0 7px rgba(234, 217, 165, .14), 0 0 36px rgba(255, 242, 190, .34);
            animation: rsvpBadgePulse 1.85s ease-in-out infinite;
        }

        .rsvp-section .rsvp-wrap {
            position: relative;
            z-index: 2;
            width: min(840px, calc(100% - 28px));
            padding: clamp(30px, 5vw, 56px);
            color: #fff;
            background: linear-gradient(180deg, rgba(7, 16, 31, .78), rgba(17, 29, 49, .66));
            border: 2px solid rgba(234, 217, 165, .76);
            border-radius: 30px;
            box-shadow: 0 28px 92px rgba(7, 16, 31, .38), 0 0 0 8px rgba(234, 217, 165, .10), inset 0 1px 0 rgba(255, 255, 255, .16);
            backdrop-filter: blur(16px);
        }

        .rsvp-section .t-scr {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 14px;
            color: #1f2d44 !important;
            background: rgba(255, 246, 219, .95);
            border-radius: 999px;
            box-shadow: 0 10px 28px rgba(7, 16, 31, .22);
        }

        .rsvp-section .t-scr::before,
        .rsvp-section .t-scr::after {
            content: "✦";
            color: #a37b24;
        }

        .rsvp-section .t-h {
            color: #fff !important;
            font-size: clamp(32px, 7vw, 56px) !important;
            font-weight: 700;
            line-height: 1.04;
            text-shadow: 0 3px 10px rgba(0, 0, 0, .45), 0 0 22px rgba(255, 255, 255, .24), 0 0 42px rgba(234, 217, 165, .22);
        }

        .rsvp-section .rsvp-note {
            width: min(620px, 100%);
            margin: 0 auto 26px;
            padding: 16px 18px;
            color: rgba(255, 255, 255, .96) !important;
            font-size: clamp(16px, 3vw, 19px);
            font-weight: 500;
            line-height: 1.72;
            background: rgba(255, 255, 255, .11);
            border: 1px solid rgba(255, 255, 255, .24);
            border-radius: 18px;
        }

        .rsvp-section .rsvp-form {
            padding: clamp(22px, 4vw, 34px) !important;
            background: linear-gradient(180deg, rgba(255, 255, 255, .18), rgba(255, 255, 255, .10)) !important;
            border: 2px solid rgba(255, 246, 219, .72) !important;
            border-radius: 24px;
            box-shadow: 0 24px 66px rgba(0, 0, 0, .24), 0 0 0 5px rgba(255, 246, 219, .08), inset 0 1px 0 rgba(255, 255, 255, .18) !important;
        }

        .rsvp-section .rsvp-form legend {
            color: #fff6db !important;
            font-size: clamp(17px, 3vw, 22px);
            font-weight: 700;
        }

        .rsvp-section .rsvp-form label,
        .rsvp-section .rsvp-form .form-hint,
        .rsvp-section .rsvp-form > p:last-child {
            color: rgba(255, 255, 255, .92) !important;
        }

        .rsvp-section .rsvp-submit {
            min-height: 56px;
            margin-top: 8px;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            box-shadow: 0 16px 44px rgba(7, 16, 31, .30), 0 0 0 6px rgba(234, 217, 165, .14), 0 0 34px rgba(234, 217, 165, .30);
            animation: rsvpSubmitPulse 1.6s ease-in-out infinite;
        }

        @keyframes rsvpBadgePulse {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-3px) scale(1.018); }
        }

        @keyframes rsvpSubmitPulse {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }

        @media (max-width: 640px) {
            .rsvp-section::before {
                width: calc(100% - 22px);
                border-radius: 18px;
                font-size: 12px;
                letter-spacing: .04em;
            }
            .rsvp-section .rsvp-wrap {
                width: calc(100% - 18px);
                padding: 24px 16px;
                border-radius: 22px;
            }
        }
    `;
    document.head.appendChild(style);
}

function buildStars(container, count) {
    if (!container) {
        return;
    }

    for (let i = 0; i < count; i++) {
        const star = document.createElement('i');
        star.style.left = `${Math.random() * 100}%`;
        star.style.top = `${Math.random() * 100}%`;
        star.style.setProperty('--size', `${1 + Math.random() * 3}px`);
        star.style.setProperty('--alpha', `${0.35 + Math.random() * 0.65}`);
        star.style.setProperty('--duration', `${2 + Math.random() * 4}s`);
        star.style.setProperty('--delay', `${Math.random() * 4}s`);
        container.appendChild(star);
    }
}

function confetti() {
    const colors = ['#b8cce4', '#d4e2f0', '#e4edf6', '#94a8c4', '#f5f0e8', '#ffffff', '#d5d5d8'];

    for (let i = 0; i < 80; i++) {
        const piece = document.createElement('div');
        const size = 5 + Math.random() * 9;
        const duration = 3000 + Math.random() * 4000;
        const drift = (Math.random() - 0.5) * 300;

        piece.className = 'cnf';
        piece.style.width = `${size}px`;
        piece.style.height = `${size}px`;
        piece.style.background = colors[Math.floor(Math.random() * colors.length)];
        piece.style.left = `${Math.random() * 100}vw`;
        piece.style.top = '-10px';
        piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
        document.body.appendChild(piece);
        piece.animate([
            { transform: 'translateY(0) translateX(0) rotate(0deg)', opacity: 0.8 },
            { transform: `translateY(${window.innerHeight + 100}px) translateX(${drift}px) rotate(${Math.random() * 720}deg)`, opacity: 0 }
        ], { duration, easing: 'cubic-bezier(.25,.46,.45,.94)' });
        window.setTimeout(() => piece.remove(), duration);
    }
}

function seekMusicToFirstStart() {
    if (!bgMusic || bgMusic.dataset.startedOnce || !bgMusic.dataset.startAt) {
        return;
    }

    try {
        bgMusic.currentTime = Number(bgMusic.dataset.startAt) || 0;
        bgMusic.dataset.startedOnce = '1';
    } catch (error) {
        bgMusic.addEventListener('loadedmetadata', seekMusicToFirstStart, { once: true });
    }
}

function playBgMusic() {
    if (!bgMusic) {
        return Promise.reject(new Error('Music element not found'));
    }

    seekMusicToFirstStart();
    bgMusic.volume = 0.35;

    return bgMusic.play().then(() => {
        musicButton?.classList.add('is-playing');
    });
}

function tryPlayBgMusic() {
    if (!bgMusic || !bgMusic.paused) {
        return;
    }

    playBgMusic().catch(() => {
        // Браузер може блокувати звук без дії користувача. Наступний клік/тап/скрол повторить запуск.
    });
}

function bindMusicAutostart() {
    if (!bgMusic || bgMusic.dataset.autostartBound === '1') {
        return;
    }

    bgMusic.dataset.autostartBound = '1';

    const startOnce = () => {
        tryPlayBgMusic();
    };

    document.addEventListener('pointerdown', startOnce, { once: true, capture: true });
    document.addEventListener('touchstart', startOnce, { once: true, capture: true });
    document.addEventListener('keydown', startOnce, { once: true, capture: true });
    document.addEventListener('wheel', startOnce, { once: true, passive: true, capture: true });
    document.addEventListener('scroll', startOnce, { once: true, passive: true, capture: true });
    window.addEventListener('scroll', startOnce, { once: true, passive: true, capture: true });
}

bindMusicAutostart();

if (musicButton && bgMusic) {
    musicButton.addEventListener('click', () => {
        if (bgMusic.paused) {
            playBgMusic().catch(() => { });
        } else {
            bgMusic.pause();
            musicButton.classList.remove('is-playing');
        }
    });

    bgMusic.addEventListener('ended', () => {
        bgMusic.currentTime = 0;
        playBgMusic().catch(() => {
            musicButton.classList.remove('is-playing');
        });
    });
}

if (openInviteButton && inviteContent) {
    openInviteButton.addEventListener('pointerdown', tryPlayBgMusic, { capture: true });

    openInviteButton.addEventListener('click', () => {
        const opening = document.querySelector('.invite-opening');
        document.body.classList.add('invite-opened');
        tryPlayBgMusic();

        inviteContent.style.display = '';
        inviteContent.style.opacity = '1';
        inviteContent.style.transform = 'none';
        inviteContent.style.visibility = 'visible';

        if (opening) {
            opening.animate([
                { opacity: 1, transform: 'translateY(0)' },
                { opacity: 0, transform: 'translateY(-18px)' }
            ], {
                duration: 520,
                easing: 'ease',
                fill: 'forwards'
            });

            window.setTimeout(() => {
                opening.style.display = 'none';
                window.scrollTo({ top: 0, behavior: 'auto' });
            }, 520);
        }
    });
}

if (copyLinkButton) {
    copyLinkButton.addEventListener('click', async () => {
        const link = copyLinkButton.dataset.copyLink || window.location.href;
        const originalText = copyLinkButton.textContent;

        try {
            await navigator.clipboard.writeText(link);
            copyLinkButton.textContent = 'Посилання скопійовано';
        } catch (error) {
            window.prompt('Скопіюйте посилання', link);
        }

        window.setTimeout(() => {
            copyLinkButton.textContent = originalText;
        }, 1800);
    });
}

const countdown = document.querySelector('[data-countdown]');

function plural(n, one, few, many) {
    const abs = Math.abs(n) % 100;
    const last = abs % 10;

    if (abs > 10 && abs < 20) {
        return many;
    }

    if (last === 1) {
        return one;
    }

    if (last > 1 && last < 5) {
        return few;
    }

    return many;
}

if (countdown) {
    const target = new Date(countdown.dataset.countdown).getTime();
    const totalMs = 150 * 86400000;
    const daysEl = countdown.querySelector('[data-days]');
    const hoursEl = countdown.querySelector('[data-hours]');
    const minutesEl = countdown.querySelector('[data-minutes]');
    const secondsEl = countdown.querySelector('[data-seconds]');
    const labels = countdown.querySelectorAll('span');
    const sandTop = document.querySelector('#sandTop');
    const sandBot = document.querySelector('#sandBot');

    function updateSand(diff) {
        if (!sandTop || !sandBot) {
            return;
        }

        const p = Math.min(1, Math.max(0, diff / totalMs));
        const wave = Math.sin(Date.now() / 800) * 2;
        const topY = 44 + (1 - p) * 94;

        if (p > 0.02) {
            sandTop.setAttribute('d', `M20,${topY} Q60,${topY - 4 + wave} 90,${topY + 2 + wave} Q120,${topY - 3 - wave} 160,${topY} L160,140 L20,140Z`);
        } else {
            sandTop.setAttribute('d', '');
        }

        const botY = 236 - (1 - p) * 88;
        const bWave = Math.sin(Date.now() / 900 + 1) * 2;

        if (p < 0.98) {
            sandBot.setAttribute('d', `M20,${botY} Q60,${botY - 3 + bWave} 90,${botY + 1 + bWave} Q120,${botY - 2 - bWave} 160,${botY} L160,240 L20,240Z`);
        } else {
            sandBot.setAttribute('d', '');
        }
    }

    function tick() {
        const diff = Math.max(0, target - Date.now());
        const days = Math.floor(diff / 86400000);
        const hours = Math.floor((diff % 86400000) / 3600000);
        const minutes = Math.floor((diff % 3600000) / 60000);
        const seconds = Math.floor((diff % 60000) / 1000);

        daysEl.textContent = String(days).padStart(2, '0');
        hoursEl.textContent = String(hours).padStart(2, '0');
        minutesEl.textContent = String(minutes).padStart(2, '0');
        secondsEl.textContent = String(seconds).padStart(2, '0');

        labels[0].textContent = plural(days, 'день', 'дні', 'днів');
        labels[1].textContent = plural(hours, 'година', 'години', 'годин');
        labels[2].textContent = plural(minutes, 'хвилина', 'хвилини', 'хвилин');
        labels[3].textContent = plural(seconds, 'секунда', 'секунди', 'секунд');
        updateSand(diff);
    }

    tick();
    window.setInterval(tick, 1000);

    function animateSand() {
        updateSand(Math.max(0, target - Date.now()));
        window.requestAnimationFrame(animateSand);
    }

    if (sandTop && sandBot) {
        window.requestAnimationFrame(animateSand);
    }
}

const rsvpForm = document.querySelector('.rsvp-form');
const plusOneRadios = document.querySelectorAll('input[name="plus_one"]');
const plusOneName = document.querySelector('.plus-one-name');
const plusOneNameInput = plusOneName?.querySelector('input[name="plus_one_name"]');
const partnerDrink = document.querySelector('.partner-drink');
const partnerDrinkSelect = partnerDrink?.querySelector('select[name="partner_drink"]');
const mainDrinkSelect = document.querySelector('select[name="drink"]');
const partnerDrinkName = document.querySelector('[data-partner-drink-name]');
const attendanceRadios = document.querySelectorAll('input[name="will_attend"]');
const coupleAttendanceRadios = document.querySelectorAll('[data-couple-attendance]');
const rsvpExtra = document.querySelector('[data-rsvp-extra]');
const rsvpSubmit = document.querySelector('[data-rsvp-submit]');
const coupleWillAttend = document.querySelector('[data-couple-will-attend]');
const couplePlusOne = document.querySelector('[data-couple-plus-one]');
let rsvpYesConfettiShown = false;

function setSelectCustomMessage(select, message) {
    if (!select) {
        return;
    }

    select.addEventListener('invalid', () => {
        if (select.value === '') {
            select.setCustomValidity(message);
        }
    });
    select.addEventListener('change', () => select.setCustomValidity(''));
    select.addEventListener('input', () => select.setCustomValidity(''));
}

setSelectCustomMessage(mainDrinkSelect, 'Будь ласка, оберіть свій напій.');
setSelectCustomMessage(partnerDrinkSelect, 'Будь ласка, оберіть напій для партнера');

if (plusOneNameInput) {
    plusOneNameInput.addEventListener('invalid', () => {
        if (plusOneNameInput.value.trim() === '') {
            plusOneNameInput.setCustomValidity('Будь ласка, вкажіть ім’я супутника.');
        }
    });

    plusOneNameInput.addEventListener('input', () => plusOneNameInput.setCustomValidity(''));
}

function getRsvpState() {
    const coupleSelected = document.querySelector('[data-couple-attendance]:checked');

    if (coupleSelected) {
        return {
            hasAnswer: true,
            willAttend: coupleSelected.value !== 'none',
            primaryAttends: coupleSelected.value === 'both' || coupleSelected.value === 'primary',
            partnerAttends: coupleSelected.value === 'both' || coupleSelected.value === 'partner',
            isCouple: true
        };
    }

    const selected = document.querySelector('input[name="will_attend"]:checked');
    const willAttend = selected?.value === '1';
    const selectedPlusOne = document.querySelector('input[name="plus_one"]:checked');
    const partnerAttends = willAttend && selectedPlusOne?.value === '1';

    return {
        hasAnswer: Boolean(selected),
        willAttend,
        primaryAttends: willAttend,
        partnerAttends,
        isCouple: false
    };
}

function syncRsvpVisibility() {
    const state = getRsvpState();

    if (coupleWillAttend) {
        coupleWillAttend.value = state.willAttend ? '1' : '0';
    }

    if (couplePlusOne) {
        couplePlusOne.value = state.partnerAttends ? '1' : '0';
    }

    if (rsvpExtra) {
        rsvpExtra.classList.toggle('is-hidden', !state.willAttend);
    }

    if (mainDrinkSelect) {
        mainDrinkSelect.required = state.primaryAttends;

        if (!state.primaryAttends) {
            mainDrinkSelect.value = '';
        }
    }

    if (plusOneName) {
        plusOneName.classList.toggle('is-hidden', !state.partnerAttends || state.isCouple);
    }

    if (plusOneNameInput) {
        plusOneNameInput.required = state.partnerAttends && !state.isCouple;
    }

    if (partnerDrink && !partnerDrink.dataset.alwaysVisible) {
        partnerDrink.classList.toggle('is-hidden', !state.partnerAttends);
    }

    if (partnerDrinkSelect) {
        partnerDrinkSelect.required = state.partnerAttends;

        if (!state.partnerAttends) {
            partnerDrinkSelect.value = '';
        }
    }

    if (partnerDrinkName && plusOneNameInput && !partnerDrink?.dataset.alwaysVisible) {
        partnerDrinkName.textContent = plusOneNameInput.value.trim() || 'партнер / супутник';
    }

    if (rsvpSubmit) {
        rsvpSubmit.classList.toggle('is-hidden', !state.hasAnswer);
        rsvpSubmit.textContent = state.willAttend ? 'Підтвердити подорож' : 'Шкода, але життя вносить свої корективи';
    }
}

if (rsvpForm) {
    rsvpForm.addEventListener('submit', (event) => {
        syncRsvpVisibility();

        if (!rsvpForm.checkValidity()) {
            event.preventDefault();
            rsvpForm.reportValidity();
        }
    });
}

plusOneRadios.forEach((radio) => radio.addEventListener('change', syncRsvpVisibility));
plusOneNameInput?.addEventListener('input', syncRsvpVisibility);

attendanceRadios.forEach((radio) => {
    radio.addEventListener('change', () => {
        syncRsvpVisibility();

        if (radio.value === '1' && radio.checked && !rsvpYesConfettiShown) {
            rsvpYesConfettiShown = true;
            confetti();
        }
    });
});

coupleAttendanceRadios.forEach((radio) => {
    radio.addEventListener('change', () => {
        syncRsvpVisibility();

        if (radio.value !== 'none' && radio.checked && !rsvpYesConfettiShown) {
            rsvpYesConfettiShown = true;
            confetti();
        }
    });
});

syncRsvpVisibility();

const revealItems = document.querySelectorAll('.reveal');

if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    revealItems.forEach((item) => observer.observe(item));
} else {
    revealItems.forEach((item) => item.classList.add('is-visible'));
}

const rsvpErrorModal = document.querySelector('[data-rsvp-error-modal]');
const rsvpErrorCloseButtons = document.querySelectorAll('[data-close-rsvp-error]');

rsvpErrorCloseButtons.forEach((button) => {
    button.addEventListener('click', () => {
        rsvpErrorModal?.classList.remove('is-visible');
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        rsvpErrorModal?.classList.remove('is-visible');
    }
});

installRsvpHighlightStyles();
buildStars(document.querySelector('.starfield'), 90);

if (document.querySelector('[data-confetti-on-load]')) {
    window.setTimeout(confetti, 450);
}
