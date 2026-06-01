document.documentElement.classList.add('js-ready');
document.documentElement.classList.remove('no-js');

const openInviteButton = document.querySelector('[data-open-invite]');
const inviteContent = document.querySelector('#inviteContent');
const copyLinkButton = document.querySelector('[data-copy-link]');
const musicButton = document.querySelector('[data-music-toggle]');
const bgMusic = document.querySelector('[data-bg-music]');
let bgMusicPendingGesture = false;

function buildStars(container, count) {
    if (!container) {
        return;
    }

    const isCelestialInvite = document.body.classList.contains('celestial-theme');
    const starCount = isCelestialInvite ? Math.min(count, 44) : count;

    for (let i = 0; i < starCount; i++) {
        const star = document.createElement('i');
        star.style.left = `${Math.random() * 100}%`;
        star.style.top = `${Math.random() * 100}%`;
        star.style.setProperty('--size', isCelestialInvite ? `${1 + Math.random() * 2.4}px` : `${1 + Math.random() * 3}px`);
        star.style.setProperty('--alpha', isCelestialInvite ? `${0.48 + Math.random() * 0.44}` : `${0.35 + Math.random() * 0.65}`);
        star.style.setProperty('--duration', isCelestialInvite ? `${3.8 + Math.random() * 4.8}s` : `${2 + Math.random() * 4}s`);
        star.style.setProperty('--delay', `${Math.random() * 5}s`);
        container.appendChild(star);
    }
}

function buildNightCrossStars(container, count) {
    if (!container) {
        return;
    }

    for (let i = 0; i < count; i++) {
        const star = document.createElement('span');
        star.className = 'night-cross-star';
        star.style.left = `${8 + Math.random() * 84}%`;
        star.style.top = `${6 + Math.random() * 72}%`;
        star.style.setProperty('--size', `${10 + Math.random() * 7}px`);
        star.style.setProperty('--alpha', `${0.58 + Math.random() * 0.34}`);
        star.style.setProperty('--duration', `${3.8 + Math.random() * 3.6}s`);
        star.style.setProperty('--delay', `${Math.random() * 5}s`);
        container.appendChild(star);
    }
}

function buildSplashStarbursts(container, count) {
    if (!container) {
        return;
    }

    for (let i = 0; i < count; i++) {
        const star = document.createElement('span');
        const size = 8 + Math.random() * 10;
        const glow = 0.4 + Math.random() * 0.4;
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');

        star.className = 'splash__star4';
        svg.setAttribute('width', String(size));
        svg.setAttribute('height', String(size));
        svg.setAttribute('viewBox', '0 0 20 20');
        svg.setAttribute('fill', `rgba(255,255,255,${glow.toFixed(2)})`);
        svg.setAttribute('aria-hidden', 'true');
        path.setAttribute('d', 'M10 0L11.5 7.5L20 10L11.5 12.5L10 20L8.5 12.5L0 10L8.5 7.5Z');
        svg.appendChild(path);
        star.appendChild(svg);
        star.style.left = `${Math.random() * 100}%`;
        star.style.top = `${Math.random() * 100}%`;
        star.style.setProperty('--d', `${2 + Math.random() * 3}s`);
        star.style.setProperty('--dl', `${Math.random() * 5}s`);
        star.style.setProperty('--lo', '0.15');
        star.style.setProperty('--hi', glow.toFixed(2));
        container.appendChild(star);
    }
}

function buildSplashComets(container, count) {
    if (!container) {
        return;
    }

    for (let i = 0; i < count; i++) {
        const comet = document.createElement('span');
        const width = 50 + Math.random() * 100;

        comet.className = 'splash__comet';
        comet.style.left = '0';
        comet.style.top = `${3 + Math.random() * 50}%`;
        comet.style.width = `${width}px`;
        comet.style.setProperty('--cd', `${3 + Math.random() * 4}s`);
        comet.style.setProperty('--cdel', `${i * 1.5 + Math.random() * 2}s`);
        comet.style.setProperty('--a', `${20 + Math.random() * 15}deg`);
        comet.style.setProperty('--dy', `${10 + Math.random() * 20}vh`);
        container.appendChild(comet);
    }
}

function buildCosmicEffects(container) {
    if (!container) {
        return;
    }

    const isCelestialInvite = document.body.classList.contains('celestial-theme');
    const starCount = isCelestialInvite ? 18 : 44;
    const dustCount = isCelestialInvite ? 0 : 38;
    const cometCount = isCelestialInvite ? 5 : 7;
    const orbCount = isCelestialInvite ? 4 : 12;

    for (let i = 0; i < starCount; i++) {
        const star = document.createElement('span');
        star.className = 'cosmic-star';
        star.style.left = `${Math.random() * 100}%`;
        star.style.top = `${Math.random() * 100}%`;
        star.style.setProperty('--size', isCelestialInvite ? `${1.1 + Math.random() * 2.5}px` : `${1 + Math.random() * 2.8}px`);
        star.style.setProperty('--alpha', isCelestialInvite ? `${0.28 + Math.random() * 0.28}` : `${0.35 + Math.random() * 0.55}`);
        star.style.setProperty('--duration', isCelestialInvite ? `${5.8 + Math.random() * 3.8}s` : `${2.4 + Math.random() * 4.5}s`);
        star.style.setProperty('--float', isCelestialInvite ? `${13 + Math.random() * 8}s` : `${8 + Math.random() * 11}s`);
        star.style.setProperty('--delay', `${Math.random() * 7}s`);
        star.style.setProperty('--drift-x', isCelestialInvite ? `${-5 + Math.random() * 10}px` : `${-14 + Math.random() * 28}px`);
        star.style.setProperty('--drift-y', isCelestialInvite ? `${-6 + Math.random() * 8}px` : `${-18 + Math.random() * 12}px`);
        container.appendChild(star);
    }

    for (let i = 0; i < dustCount; i++) {
        const dust = document.createElement('span');
        dust.className = 'cosmic-dust';
        dust.style.left = `${Math.random() * 100}%`;
        dust.style.top = `${-20 - Math.random() * 100}vh`;
        dust.style.setProperty('--size', `${1 + Math.random() * 2.2}px`);
        dust.style.setProperty('--alpha', `${0.18 + Math.random() * 0.36}`);
        dust.style.setProperty('--duration', `${14 + Math.random() * 18}s`);
        dust.style.setProperty('--delay', `${Math.random() * 18}s`);
        dust.style.setProperty('--drift-x', `${-60 + Math.random() * 120}px`);
        container.appendChild(dust);
    }

    for (let i = 0; i < cometCount; i++) {
        const comet = document.createElement('span');
        comet.className = 'cosmic-comet';
        comet.style.left = `${-12 - Math.random() * 18}vw`;
        comet.style.top = `${4 + Math.random() * 92}%`;
        comet.style.setProperty('--tail', isCelestialInvite ? `${90 + Math.random() * 130}px` : `${90 + Math.random() * 90}px`);
        comet.style.setProperty('--angle', `${20 + Math.random() * 16}deg`);
        comet.style.setProperty('--drop', isCelestialInvite ? `${16 + Math.random() * 36}vh` : `${18 + Math.random() * 36}vh`);
        comet.style.setProperty('--duration', isCelestialInvite ? `${6 + Math.random() * 5.5}s` : `${5.5 + Math.random() * 4.5}s`);
        comet.style.setProperty('--delay', `${i * (isCelestialInvite ? 3.2 : 2.8) + Math.random() * 5}s`);
        container.appendChild(comet);
    }

    for (let i = 0; i < orbCount; i++) {
        const orb = document.createElement('span');
        orb.className = 'cosmic-orb';
        orb.style.left = `${Math.random() * 100}%`;
        orb.style.top = `${Math.random() * 100}%`;
        orb.style.setProperty('--size', isCelestialInvite ? `${4 + Math.random() * 5}px` : `${4 + Math.random() * 9}px`);
        orb.style.setProperty('--alpha', isCelestialInvite ? `${0.12 + Math.random() * 0.14}` : `${0.16 + Math.random() * 0.3}`);
        orb.style.setProperty('--duration', isCelestialInvite ? `${14 + Math.random() * 8}s` : `${10 + Math.random() * 12}s`);
        orb.style.setProperty('--delay', `${Math.random() * 8}s`);
        orb.style.setProperty('--drift-x', isCelestialInvite ? `${-10 + Math.random() * 20}px` : `${-28 + Math.random() * 56}px`);
        orb.style.setProperty('--drift-y', isCelestialInvite ? `${-12 + Math.random() * 16}px` : `${-34 + Math.random() * 22}px`);
        container.appendChild(orb);
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

const splashStars = document.querySelector('.splash-stars');

buildStars(splashStars, 70);
buildSplashStarbursts(splashStars, 10);
buildSplashComets(splashStars, 4);
document.querySelectorAll('.starfield').forEach((starfield) => buildStars(starfield, 90));
document.querySelectorAll('.starfield').forEach((starfield) => buildNightCrossStars(starfield, 8));
document.querySelectorAll('.cosmic-effects').forEach((effects) => buildCosmicEffects(effects));

if (document.querySelector('[data-confetti-on-load]')) {
    window.setTimeout(confetti, 450);
}

if (openInviteButton && inviteContent) {
    openInviteButton.addEventListener('click', () => {
        document.body.classList.add('invite-opened');

        startBackgroundMusic();

        window.setTimeout(() => {
            inviteContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 320);
    });
} else if (inviteContent) {
    window.addEventListener('load', () => {
        startBackgroundMusic();
    }, { once: true });
}

function armMusicGestureFallback() {
    if (!bgMusic || bgMusicPendingGesture) {
        return;
    }

    bgMusicPendingGesture = true;
    const options = { once: true, passive: true };
    const playAfterGesture = () => {
        bgMusicPendingGesture = false;
        startBackgroundMusic();
    };

    window.addEventListener('pointerdown', playAfterGesture, options);
    window.addEventListener('keydown', playAfterGesture, { once: true });
    window.addEventListener('touchstart', playAfterGesture, options);
    window.addEventListener('wheel', playAfterGesture, options);
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

function startBackgroundMusic() {
    if (!bgMusic) {
        return;
    }

    seekMusicToFirstStart();
    bgMusic.volume = 0.35;
    bgMusic.play().then(() => {
        bgMusicPendingGesture = false;
        musicButton?.classList.add('is-playing');
    }).catch(() => {
        musicButton?.classList.remove('is-playing');
        armMusicGestureFallback();
    });
}

if (musicButton && bgMusic) {
    musicButton.addEventListener('click', () => {
        if (bgMusic.paused) {
            seekMusicToFirstStart();
            bgMusic.play().then(() => musicButton.classList.add('is-playing')).catch(() => { });
        } else {
            bgMusic.pause();
            musicButton.classList.remove('is-playing');
        }
    });

    bgMusic.addEventListener('ended', () => {
        bgMusic.currentTime = 0;
        bgMusic.play().then(() => musicButton.classList.add('is-playing')).catch(() => {
            musicButton.classList.remove('is-playing');
        });
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
            sandTop.setAttribute(
                'd',
                `M20,${topY} Q60,${topY - 4 + wave} 90,${topY + 2 + wave} Q120,${topY - 3 - wave} 160,${topY} L160,140 L20,140Z`
            );
        } else {
            sandTop.setAttribute('d', '');
        }

        const botY = 236 - (1 - p) * 88;
        const bWave = Math.sin(Date.now() / 900 + 1) * 2;

        if (p < 0.98) {
            sandBot.setAttribute(
                'd',
                `M20,${botY} Q60,${botY - 3 + bWave} 90,${botY + 1 + bWave} Q120,${botY - 2 - bWave} 160,${botY} L160,240 L20,240Z`
            );
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
const rsvpExtra = document.querySelector('[data-rsvp-extra]');
const rsvpSubmit = document.querySelector('[data-rsvp-submit]');
let rsvpYesConfettiShown = false;


if (mainDrinkSelect) {
    mainDrinkSelect.addEventListener('invalid', () => {
        if (mainDrinkSelect.value === '') {
            mainDrinkSelect.setCustomValidity('Будь ласка, оберіть свій напій.');
        }
    });

    mainDrinkSelect.addEventListener('change', () => {
        mainDrinkSelect.setCustomValidity('');
    });

    mainDrinkSelect.addEventListener('input', () => {
        mainDrinkSelect.setCustomValidity('');
    });
}

if (partnerDrinkSelect) {
    partnerDrinkSelect.addEventListener('invalid', () => {
        if (partnerDrinkSelect.value === '') {
            partnerDrinkSelect.setCustomValidity('Будь ласка, оберіть напій для партнера');
        }
    });

    partnerDrinkSelect.addEventListener('change', () => {
        partnerDrinkSelect.setCustomValidity('');
    });

    partnerDrinkSelect.addEventListener('input', () => {
        partnerDrinkSelect.setCustomValidity('');
    });
}

if (plusOneNameInput) {
    plusOneNameInput.addEventListener('invalid', () => {
        if (plusOneNameInput.value.trim() === '') {
            plusOneNameInput.setCustomValidity('Будь ласка, вкажіть ім’я супутника.');
        }
    });

    plusOneNameInput.addEventListener('input', () => {
        plusOneNameInput.setCustomValidity('');
    });
}


function syncRsvpVisibility() {
    const selected = document.querySelector('input[name="will_attend"]:checked');
    const willAttend = selected?.value === '1';
    const hasAnswer = Boolean(selected);

    if (rsvpExtra) {
        rsvpExtra.classList.toggle('is-hidden', !willAttend);
    }

    if (mainDrinkSelect) {
        mainDrinkSelect.required = willAttend;

        if (!willAttend) {
            mainDrinkSelect.value = '';
        }
    }

    if (!willAttend) {
        if (partnerDrinkSelect) {
            partnerDrinkSelect.required = false;
            partnerDrinkSelect.value = '';
        }

        if (plusOneNameInput) {
            plusOneNameInput.required = false;
        }
    }

    if (rsvpSubmit) {
        rsvpSubmit.classList.toggle('is-hidden', !hasAnswer);
        rsvpSubmit.textContent = willAttend
            ? 'Підтвердити подорож'
            : 'Шкода, але життя вносить свої корективи';
    }

    syncPlusOneName();
}

function syncPlusOneName() {
    const selectedAttendance = document.querySelector('input[name="will_attend"]:checked');
    const willAttend = selectedAttendance?.value === '1';

    const selectedPlusOne = document.querySelector('input[name="plus_one"]:checked');
    const hasPartner = willAttend && selectedPlusOne?.value === '1';

    if (plusOneName) {
        plusOneName.classList.toggle('is-hidden', !hasPartner);
    }

    if (plusOneNameInput) {
        plusOneNameInput.required = hasPartner;
    }

    if (partnerDrink && !partnerDrink.dataset.alwaysVisible) {
        partnerDrink.classList.toggle('is-hidden', !hasPartner);
    }

    if (partnerDrinkSelect) {
        const isAlwaysVisible = Boolean(partnerDrink?.dataset.alwaysVisible);
        partnerDrinkSelect.required = willAttend && (hasPartner || isAlwaysVisible);

        if (!partnerDrinkSelect.required) {
            partnerDrinkSelect.value = '';
        }
    }

    if (partnerDrinkName && plusOneNameInput && !partnerDrink?.dataset.alwaysVisible) {
        partnerDrinkName.textContent = plusOneNameInput.value.trim() || 'партнер / супутник';
    }
}

if (rsvpForm) {
    rsvpForm.addEventListener('submit', (event) => {
        syncRsvpVisibility();
        syncPlusOneName();

        if (!rsvpForm.checkValidity()) {
            event.preventDefault();
            rsvpForm.reportValidity();
        }
    });
}

plusOneRadios.forEach((radio) => {
    radio.addEventListener('change', syncPlusOneName);
});

plusOneNameInput?.addEventListener('input', syncPlusOneName);

attendanceRadios.forEach((radio) => {
    radio.addEventListener('change', () => {
        syncRsvpVisibility();

        if (radio.value === '1' && radio.checked && !rsvpYesConfettiShown) {
            rsvpYesConfettiShown = true;
            confetti();
        }
    });
});

syncPlusOneName();
syncRsvpVisibility();

const revealItems = document.querySelectorAll('.reveal');
revealItems[0]?.classList.add('is-visible');

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
