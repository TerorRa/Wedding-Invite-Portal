(() => {
    'use strict';

    document.documentElement.classList.add('js-ready');
    document.documentElement.classList.remove('no-js');
    document.body.classList.add('has-invite-gate');

    const openInviteButton = document.querySelector('[data-open-invite]');
    const inviteContent = document.querySelector('#inviteContent');
    const copyLinkButton = document.querySelector('[data-copy-link]');
    const musicButton = document.querySelector('[data-music-toggle]');
    const bgMusic = document.querySelector('[data-bg-music]');
    const INVITE_NIGHT_BACKGROUND = 'linear-gradient(180deg, #07101f 0%, #111d31 54%, #1b2845 100%)';

    function safeRun(fn) {
        try {
            fn();
        } catch (error) {
            console.error('[invite.js]', error);
        }
    }

    function installInviteStyles() {
        if (document.querySelector('#inviteRuntimeStyles')) {
            return;
        }

        const style = document.createElement('style');
        style.id = 'inviteRuntimeStyles';
        style.textContent = `
            body.has-invite-gate::before,
            body.has-inline-photo-intro::before {
                display: none !important;
                content: none !important;
            }

            body.has-invite-gate,
            body.has-inline-photo-intro,
            body.has-invite-gate main,
            body.has-inline-photo-intro main {
                background: #07101f !important;
            }

            body.has-invite-gate .invite-opening,
            body.has-invite-gate .invite-content,
            body.has-invite-gate .invite-photo-opening,
            body.has-invite-gate .invite-bg-night {
                background: ${INVITE_NIGHT_BACKGROUND} !important;
            }

            body.has-invite-gate .invite-content {
                min-height: 100vh;
                isolation: isolate;
            }

            body.has-invite-gate .invite-content-stars {
                position: fixed !important;
                inset: 0 !important;
                z-index: 0 !important;
                pointer-events: none !important;
                overflow: hidden !important;
                display: none;
                opacity: .98;
            }

            body.has-invite-gate.invite-opened .invite-content-stars {
                display: block !important;
            }

            body.has-invite-gate .invite-content > section,
            body.has-invite-gate .invite-content > div:not(.invite-content-stars):not(.cosmic-effects) {
                position: relative;
                z-index: 2;
            }

            body.has-invite-gate .cosmic-effects {
                z-index: 1 !important;
                opacity: .95 !important;
            }

            body.has-invite-gate .invite-shared-moon {
                position: fixed;
                top: clamp(42px, 12vh, 110px);
                right: clamp(28px, 10vw, 150px);
                z-index: 4;
                width: clamp(74px, 15vw, 156px);
                aspect-ratio: 1;
                border-radius: 50%;
                pointer-events: none;
                background: radial-gradient(circle at 36% 32%, #ffffff 0 12%, #f5f8fc 28%, #dbe6f2 52%, #9fb6d1 100%);
                box-shadow: 0 0 28px rgba(255,255,255,.78), 0 0 90px rgba(184,204,228,.52), 0 0 170px rgba(184,204,228,.22);
                opacity: .92;
            }

            body.has-invite-gate .invite-shared-moon span {
                position: absolute;
                inset: 18% 0 0 24%;
                width: 76%;
                height: 76%;
                border-radius: 50%;
                background: rgba(27,40,69,.18);
                filter: blur(1px);
                transform: rotate(-14deg);
            }

            body.has-invite-gate .splash__comet,
            body.has-inline-photo-intro .splash__comet,
            body.has-invite-gate .cosmic-comet,
            body.has-inline-photo-intro .cosmic-comet {
                opacity: 0;
                animation-fill-mode: both;
            }

            .rsvp-section {
                position: relative;
                overflow: hidden;
                isolation: isolate;
                padding-top: clamp(72px, 9vw, 128px) !important;
                padding-bottom: clamp(78px, 10vw, 142px) !important;
                color: #fff;
                background:
                    radial-gradient(circle at 14% 10%, rgba(255, 232, 178, .28), transparent 24%),
                    radial-gradient(circle at 50% 110%, rgba(201, 168, 93, .34), transparent 36%),
                    linear-gradient(180deg, #111d31 0%, #283a5a 48%, #6f86a6 100%) !important;
                border-top: 2px solid rgba(234, 217, 165, .72);
                border-bottom: 2px solid rgba(234, 217, 165, .62);
                box-shadow: inset 0 18px 80px rgba(0, 0, 0, .22), inset 0 -18px 80px rgba(0, 0, 0, .16);
            }

            .rsvp-section::before {
                content: "Важливо: будь ласка, заповніть анкету до 17.07.2026";
                position: relative;
                z-index: 4;
                display: block;
                width: min(760px, calc(100% - 36px));
                margin: 0 auto 22px;
                padding: 13px 18px;
                color: #1f2d44;
                text-align: center;
                font-size: clamp(13px, 2.8vw, 16px);
                font-weight: 800;
                letter-spacing: .08em;
                line-height: 1.35;
                text-transform: uppercase;
                background: linear-gradient(135deg, #fff6db, #ead9a5 48%, #fff8e8 100%);
                border: 1px solid rgba(255, 255, 255, .78);
                border-radius: 999px;
                box-shadow: 0 12px 38px rgba(31, 45, 68, .28), 0 0 0 7px rgba(234, 217, 165, .14), 0 0 36px rgba(255, 242, 190, .34);
                animation: rsvpUrgentBadgePulse 1.85s ease-in-out infinite;
            }

            .rsvp-section::after {
                content: "";
                position: absolute;
                inset: 18px;
                z-index: 0;
                pointer-events: none;
                border: 1px solid rgba(234, 217, 165, .44);
                border-radius: 28px;
                box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .08), 0 0 54px rgba(234, 217, 165, .16);
            }

            .rsvp-section .rsvp-wrap {
                position: relative;
                z-index: 3;
                width: min(840px, calc(100% - 28px));
                padding: clamp(30px, 5vw, 56px);
                background: radial-gradient(circle at 50% 0%, rgba(255, 255, 255, .20), transparent 38%), linear-gradient(180deg, rgba(7, 16, 31, .78), rgba(17, 29, 49, .66));
                border: 2px solid rgba(234, 217, 165, .76);
                border-radius: 30px;
                box-shadow: 0 28px 92px rgba(7, 16, 31, .38), 0 0 0 8px rgba(234, 217, 165, .10), inset 0 1px 0 rgba(255, 255, 255, .16);
                backdrop-filter: blur(16px);
            }

            .rsvp-section .section-symbol--ticket {
                opacity: .44 !important;
                filter: drop-shadow(0 0 18px rgba(255, 255, 255, .7)) drop-shadow(0 0 42px rgba(234, 217, 165, .38));
                animation: rsvpStarShake 2.8s ease-in-out infinite;
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
                font-size: clamp(32px, 7vw, 58px) !important;
                font-weight: 700;
                line-height: 1.03;
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
                background: radial-gradient(circle at 10% 0%, rgba(255, 246, 219, .18), transparent 28%), linear-gradient(180deg, rgba(255, 255, 255, .18), rgba(255, 255, 255, .10)) !important;
                border: 2px solid rgba(255, 246, 219, .72) !important;
                border-radius: 24px;
                box-shadow: 0 24px 66px rgba(0, 0, 0, .24), 0 0 0 5px rgba(255, 246, 219, .08), inset 0 1px 0 rgba(255, 255, 255, .18) !important;
            }

            .rsvp-section .rsvp-form legend {
                color: #fff6db !important;
                font-size: clamp(17px, 3vw, 22px);
                font-weight: 700;
            }

            .rsvp-section .rsvp-form fieldset {
                background: rgba(7, 16, 31, .30) !important;
                border-color: rgba(234, 217, 165, .42) !important;
            }

            .rsvp-section .rsvp-form label,
            .rsvp-section .rsvp-form .form-hint {
                color: rgba(255, 255, 255, .94) !important;
            }

            .rsvp-section .rsvp-form fieldset label {
                border-color: rgba(234, 217, 165, .30) !important;
                background: rgba(255, 255, 255, .10) !important;
            }

            .rsvp-section .drink-select,
            .rsvp-section .rsvp-form input[type="text"],
            .rsvp-section .rsvp-form textarea {
                border-width: 2px !important;
                border-color: rgba(234, 217, 165, .70) !important;
                box-shadow: 0 10px 22px rgba(7, 16, 31, .16);
            }

            .rsvp-section .rsvp-submit {
                min-height: 56px;
                margin-top: 8px;
                font-size: 16px;
                font-weight: 800;
                letter-spacing: .06em;
                text-transform: uppercase;
                border: 1px solid rgba(255, 255, 255, .70);
                box-shadow: 0 16px 44px rgba(7, 16, 31, .30), 0 0 0 6px rgba(234, 217, 165, .14), 0 0 34px rgba(234, 217, 165, .30);
                animation: rsvpSubmitPulse 1.6s ease-in-out infinite;
            }

            .rsvp-section .rsvp-form > p:last-child {
                color: rgba(255, 255, 255, .76);
                font-size: 12px;
                line-height: 1.55;
            }

            @keyframes rsvpUrgentBadgePulse {
                0%, 100% { transform: translateY(0) scale(1); }
                50% { transform: translateY(-3px) scale(1.018); }
            }

            @keyframes rsvpSubmitPulse {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-3px); }
            }

            @keyframes rsvpStarShake {
                0%, 100% { transform: translate3d(0, 0, 0) rotate(0deg); }
                25% { transform: translate3d(0, -4px, 0) rotate(5deg); }
                50% { transform: translate3d(0, 0, 0) rotate(-3deg); }
                75% { transform: translate3d(0, -3px, 0) rotate(4deg); }
            }

            @media (max-width: 640px) {
                .rsvp-section { padding-top: 64px !important; padding-bottom: 82px !important; }
                .rsvp-section::before { width: calc(100% - 22px); border-radius: 18px; font-size: 12px; letter-spacing: .04em; }
                .rsvp-section::after { inset: 10px; border-radius: 20px; }
                .rsvp-section .rsvp-wrap { width: calc(100% - 18px); padding: 24px 16px; border-radius: 22px; }
            }
        `;
        document.head.appendChild(style);
    }

    function ensureInviteSharedMoon() {
        if (document.querySelector('.invite-shared-moon')) {
            return;
        }

        const moon = document.createElement('div');
        const shadow = document.createElement('span');
        moon.className = 'invite-shared-moon';
        moon.setAttribute('aria-hidden', 'true');
        shadow.setAttribute('aria-hidden', 'true');
        moon.appendChild(shadow);
        document.body.appendChild(moon);
    }

    function buildStars(container, count) {
        if (!container) return;

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

    function buildSplashStarbursts(container, count) {
        if (!container) return;

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
        if (!container) return;

        for (let i = 0; i < count; i++) {
            const comet = document.createElement('span');
            const width = 50 + Math.random() * 100;
            comet.className = 'splash__comet';
            comet.style.left = `${-(width + 180)}px`;
            comet.style.top = `${-12 + Math.random() * 62}%`;
            comet.style.width = `${width}px`;
            comet.style.opacity = '0';
            comet.style.animationFillMode = 'both';
            comet.style.setProperty('--cd', `${3 + Math.random() * 4}s`);
            comet.style.setProperty('--cdel', `${i * 1.5 + Math.random() * 2}s`);
            comet.style.setProperty('--a', `${20 + Math.random() * 15}deg`);
            comet.style.setProperty('--dy', `${10 + Math.random() * 20}vh`);
            container.appendChild(comet);
        }
    }

    function buildCosmicEffects(container) {
        if (!container) return;

        for (let i = 0; i < 44; i++) {
            const star = document.createElement('span');
            star.className = 'cosmic-star';
            star.style.left = `${Math.random() * 100}%`;
            star.style.top = `${Math.random() * 100}%`;
            star.style.setProperty('--size', `${1 + Math.random() * 2.8}px`);
            star.style.setProperty('--alpha', `${0.35 + Math.random() * 0.55}`);
            star.style.setProperty('--duration', `${2.4 + Math.random() * 4.5}s`);
            star.style.setProperty('--float', `${8 + Math.random() * 11}s`);
            star.style.setProperty('--delay', `${Math.random() * 7}s`);
            star.style.setProperty('--drift-x', `${-14 + Math.random() * 28}px`);
            star.style.setProperty('--drift-y', `${-18 + Math.random() * 12}px`);
            container.appendChild(star);
        }

        for (let i = 0; i < 38; i++) {
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

        for (let i = 0; i < 7; i++) {
            const comet = document.createElement('span');
            comet.className = 'cosmic-comet';
            comet.style.left = `${-35 - Math.random() * 24}vw`;
            comet.style.top = `${-10 + Math.random() * 112}%`;
            comet.style.opacity = '0';
            comet.style.animationFillMode = 'both';
            comet.style.setProperty('--tail', `${90 + Math.random() * 90}px`);
            comet.style.setProperty('--angle', `${20 + Math.random() * 16}deg`);
            comet.style.setProperty('--drop', `${18 + Math.random() * 36}vh`);
            comet.style.setProperty('--duration', `${5.5 + Math.random() * 4.5}s`);
            comet.style.setProperty('--delay', `${i * 2.8 + Math.random() * 5}s`);
            container.appendChild(comet);
        }

        for (let i = 0; i < 12; i++) {
            const orb = document.createElement('span');
            orb.className = 'cosmic-orb';
            orb.style.left = `${Math.random() * 100}%`;
            orb.style.top = `${Math.random() * 100}%`;
            orb.style.setProperty('--size', `${4 + Math.random() * 9}px`);
            orb.style.setProperty('--alpha', `${0.16 + Math.random() * 0.3}`);
            orb.style.setProperty('--duration', `${10 + Math.random() * 12}s`);
            orb.style.setProperty('--delay', `${Math.random() * 8}s`);
            orb.style.setProperty('--drift-x', `${-28 + Math.random() * 56}px`);
            orb.style.setProperty('--drift-y', `${-34 + Math.random() * 22}px`);
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

    function getMemoryCardPlacement(index) {
        const zones = [
            { left: [4, 18], top: [7, 20] },
            { left: [68, 82], top: [8, 22] },
            { left: [6, 20], top: [58, 74] },
            { left: [68, 82], top: [58, 74] },
            { left: [22, 36], top: [5, 18] },
            { left: [54, 68], top: [20, 34] },
            { left: [24, 38], top: [68, 80] },
            { left: [54, 68], top: [70, 80] }
        ];
        const zone = zones[index % zones.length];
        const left = zone.left[0] + Math.random() * (zone.left[1] - zone.left[0]);
        const top = zone.top[0] + Math.random() * (zone.top[1] - zone.top[0]);
        const fromLeft = left < 45;
        const fromTop = top < 45;
        const sign = Math.random() > 0.5 ? 1 : -1;

        return {
            left: left.toFixed(2),
            top: top.toFixed(2),
            startX: `${(fromLeft ? -1 : 1) * (34 + Math.random() * 34)}vw`,
            startY: `${(fromTop ? -1 : 1) * (30 + Math.random() * 32)}vh`,
            startR: `${sign * (22 + Math.random() * 26)}deg`,
            endR: `${(Math.random() > 0.5 ? 1 : -1) * (3 + Math.random() * 8)}deg`
        };
    }

    function applyFloatingPhotoStyles(card, placement, index = 0, isRing = false, delayMs = null) {
        const size = isRing ? 'clamp(260px, 42vw, 500px)' : 'clamp(126px, 20vw, 220px)';
        const borderWidth = isRing ? '8px' : '6px';
        const startTransform = isRing
            ? 'translate(-50%, -50%) scale(0.72) rotateY(-22deg) rotateZ(8deg)'
            : `translate3d(${placement.startX}, ${placement.startY}, -220px) rotateX(24deg) rotateY(-30deg) rotateZ(${placement.startR}) scale(0.66)`;
        const endTransform = isRing
            ? 'translate(-50%, -50%) scale(1) rotateY(0) rotateZ(0)'
            : `translate3d(0, 0, 0) rotateX(0) rotateY(0) rotateZ(${placement.endR}) scale(1)`;
        const restTransform = isRing
            ? endTransform
            : `translate3d(0, 0, -40px) rotateX(0) rotateY(0) rotateZ(${placement.endR}) scale(0.92)`;

        Object.assign(card.style, {
            position: 'absolute',
            zIndex: isRing ? '8' : '2',
            left: isRing ? '50%' : `${placement.left}%`,
            top: isRing ? '38%' : `${placement.top}%`,
            width: size,
            aspectRatio: '0.78',
            margin: '0',
            overflow: 'hidden',
            backgroundColor: 'rgba(255, 255, 255, 0.34)',
            border: `${borderWidth} solid rgba(238, 243, 246, 0.76)`,
            borderRadius: '12px',
            boxShadow: '0 18px 44px rgba(64, 88, 122, 0.18), inset 0 0 0 1px rgba(255, 255, 255, 0.24)',
            opacity: '0',
            transform: startTransform,
            transformStyle: 'preserve-3d',
            pointerEvents: 'none',
            boxSizing: 'border-box'
        });

        window.setTimeout(() => {
            card.animate([
                { opacity: 0, transform: startTransform },
                { opacity: 1, offset: 0.32 },
                { opacity: 1, transform: endTransform, offset: isRing ? 1 : 0.74 },
                { opacity: isRing ? 1 : 0.34, transform: restTransform }
            ], {
                duration: isRing ? 1600 : 7200,
                easing: 'cubic-bezier(0.2, 0.72, 0.18, 1)',
                fill: 'forwards'
            });
        }, delayMs ?? (isRing ? 0 : 250 + index * 520));
    }

    function createPhotoCard(src, index) {
        const placement = getMemoryCardPlacement(index);
        const card = document.createElement('figure');
        const img = document.createElement('img');
        card.className = 'invite-photo-card';
        img.src = src;
        img.alt = '';
        img.loading = index < 2 ? 'eager' : 'lazy';
        img.decoding = 'async';
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.objectFit = 'cover';
        img.style.display = 'block';
        card.appendChild(img);
        applyFloatingPhotoStyles(card, placement, index, false);
        return card;
    }

    function makeIntroOpenButton(button) {
        button.className = 'section-action btn-p';
        button.innerHTML = 'Перейти до запрошення';
        button.setAttribute('aria-label', 'Перейти до запрошення');
        button.type = 'button';
        button.style.marginTop = '26px';
        return button;
    }

    function applyDawnThemeToInvite() {
        document.body.classList.add('invite-opened');
    }

    async function setupIntegratedInviteIntro() {
        const opening = document.querySelector('.invite-opening');
        if (!opening || !openInviteButton) return;

        const code = new URLSearchParams(window.location.search).get('code') || '';
        if (!code) return;

        try {
            const response = await fetch(`intro_assets.php?code=${encodeURIComponent(code)}`, { cache: 'no-store' });
            const data = await response.json();
            if (!data.ok || ((!Array.isArray(data.photos) || data.photos.length === 0) && !data.ring)) return;

            document.body.classList.add('has-inline-photo-intro');
            if (inviteContent) inviteContent.style.display = 'none';

            Object.assign(opening.style, {
                position: 'relative',
                inset: 'auto',
                zIndex: '2',
                minHeight: '100svh',
                overflow: 'hidden',
                opacity: '1',
                visibility: 'visible',
                transform: 'none',
                background: INVITE_NIGHT_BACKGROUND
            });

            const button = makeIntroOpenButton(openInviteButton);
            const sky = document.createElement('div');
            const memoryStage = document.createElement('div');
            const final = document.createElement('div');
            sky.className = 'splash-stars';
            sky.setAttribute('aria-hidden', 'true');
            memoryStage.setAttribute('aria-hidden', 'true');
            Object.assign(memoryStage.style, {
                position: 'absolute',
                inset: '0',
                pointerEvents: 'none',
                perspective: '1100px',
                zIndex: '2'
            });

            (data.photos || []).forEach((photo, index) => memoryStage.appendChild(createPhotoCard(photo, index)));
            opening.innerHTML = '';
            opening.appendChild(sky);
            opening.appendChild(memoryStage);
            buildStars(sky, 120);
            buildSplashStarbursts(sky, 24);
            buildSplashComets(sky, 8);

            if (data.ring) {
                const ring = document.createElement('figure');
                const img = document.createElement('img');
                ring.className = 'invite-photo-card invite-photo-card--ring';
                ring.setAttribute('aria-hidden', 'true');
                img.src = data.ring;
                img.alt = '';
                img.loading = 'eager';
                img.decoding = 'async';
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
                img.style.display = 'block';
                ring.appendChild(img);
                applyFloatingPhotoStyles(ring, { startX: '0vw', startY: '-18vh', startR: '8deg', endR: '0deg' }, 0, true, Number(data.ringDelay || 5.1) * 1000);
                opening.appendChild(ring);
            }

            final.className = 'welcome invite-intro-message';
            Object.assign(final.style, {
                position: 'relative',
                zIndex: '12',
                width: 'min(720px, 100%)',
                marginTop: 'min(52vh, 410px)',
                textAlign: 'center',
                opacity: '0',
                transform: 'translateY(24px) scale(0.96)',
                background: 'rgba(255, 255, 255, 0.72)',
                border: '1px solid var(--line)',
                borderRadius: 'var(--radius)',
                boxShadow: 'var(--shadow)',
                backdropFilter: 'blur(14px)',
                padding: 'clamp(28px, 5vw, 44px)'
            });
            final.innerHTML = `
                <p style="margin:0 0 18px;color:var(--dusty);font-size:12px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;">${data.guestName ? `${data.guestName},` : 'Дорогий гостю,'}</p>
                <div style="width:min(620px,100%);margin:0 auto;color:var(--text);font-family:'Cormorant Garamond', Georgia, serif;font-size:clamp(22px,3.6vw,34px);font-weight:500;line-height:1.32;">
                    <p style="margin:0 0 10px;">Здавна люди вірили, що кожна зірка на небі — це чиясь доля.</p>
                    <p style="margin:0 0 10px;">Дві долі, що знайшли одна одну, зливаються в одне світло —</p>
                    <p style="margin:0 0 14px;">і на небосхилі спалахує нова зірочка.</p>
                    <p style="margin:0 0 10px;">Незабаром така з'явиться і в нас.</p>
                    <p style="margin:0;">Хочемо, щоб саме ви були поруч,<br>коли вона засяє вперше. ✨</p>
                </div>
            `;
            final.appendChild(button);
            opening.appendChild(final);

            window.setTimeout(() => {
                final.animate([
                    { opacity: 0, transform: 'translateY(24px) scale(0.96)' },
                    { opacity: 1, transform: 'translateY(0) scale(1)' }
                ], { duration: 1100, easing: 'ease', fill: 'forwards' });
            }, Number(data.finalDelay || 6.15) * 1000);
        } catch (error) {
            console.warn('[invite.js] intro assets fallback', error);
        }
    }

    function setupStarLayers() {
        const splashStars = document.querySelector('.splash-stars');
        buildStars(splashStars, 120);
        buildSplashStarbursts(splashStars, 24);
        buildSplashComets(splashStars, 8);
        buildStars(document.querySelector('.starfield'), 90);

        if (!document.querySelector('.invite-content-stars')) {
            const contentStars = document.createElement('div');
            contentStars.className = 'splash-stars invite-content-stars';
            contentStars.setAttribute('aria-hidden', 'true');
            document.body.appendChild(contentStars);
            buildStars(contentStars, 120);
            buildSplashStarbursts(contentStars, 24);
            buildSplashComets(contentStars, 8);
        }

        buildCosmicEffects(document.querySelector('.cosmic-effects'));
    }

    function seekMusicToFirstStart() {
        if (!bgMusic || bgMusic.dataset.startedOnce || !bgMusic.dataset.startAt) return;

        try {
            bgMusic.currentTime = Number(bgMusic.dataset.startAt) || 0;
            bgMusic.dataset.startedOnce = '1';
        } catch (error) {
            bgMusic.addEventListener('loadedmetadata', seekMusicToFirstStart, { once: true });
        }
    }

    function setupMusic() {
        if (!musicButton || !bgMusic || musicButton.dataset.bound === '1') return;
        musicButton.dataset.bound = '1';

        musicButton.addEventListener('click', () => {
            if (bgMusic.paused) {
                seekMusicToFirstStart();
                bgMusic.play()
                    .then(() => musicButton.classList.add('is-playing'))
                    .catch((error) => console.warn('[invite.js] music play blocked', error));
            } else {
                bgMusic.pause();
                musicButton.classList.remove('is-playing');
            }
        });

        bgMusic.addEventListener('ended', () => {
            bgMusic.currentTime = 0;
            bgMusic.play()
                .then(() => musicButton.classList.add('is-playing'))
                .catch(() => musicButton.classList.remove('is-playing'));
        });
    }

    function setupOpenInvite() {
        if (!openInviteButton || !inviteContent || openInviteButton.dataset.bound === '1') return;
        openInviteButton.dataset.bound = '1';

        openInviteButton.addEventListener('click', () => {
            const opening = document.querySelector('.invite-opening');
            document.body.classList.add('invite-opened');
            applyDawnThemeToInvite();

            if (bgMusic) {
                seekMusicToFirstStart();
                bgMusic.volume = 0.35;
                bgMusic.play()
                    .then(() => musicButton?.classList.add('is-playing'))
                    .catch(() => { });
            }

            inviteContent.style.display = '';
            inviteContent.style.opacity = '1';
            inviteContent.style.transform = 'none';
            inviteContent.style.visibility = 'visible';

            if (opening) {
                opening.animate([
                    { opacity: 1, transform: 'translateY(0)' },
                    { opacity: 0, transform: 'translateY(-18px)' }
                ], { duration: 520, easing: 'ease', fill: 'forwards' });
            }

            window.setTimeout(() => {
                if (opening) opening.style.display = 'none';
                inviteContent.style.opacity = '1';
                inviteContent.style.transform = 'none';
                inviteContent.style.visibility = 'visible';
                window.scrollTo({ top: 0, behavior: 'auto' });
            }, 520);
        });
    }

    function setupCountdown() {
        const countdown = document.querySelector('[data-countdown]');
        if (!countdown) return;

        const target = new Date(countdown.dataset.countdown).getTime();
        const daysEl = countdown.querySelector('[data-days]');
        const hoursEl = countdown.querySelector('[data-hours]');
        const minutesEl = countdown.querySelector('[data-minutes]');
        const secondsEl = countdown.querySelector('[data-seconds]');
        const labels = countdown.querySelectorAll('span');
        const sandTop = document.querySelector('#sandTop');
        const sandBot = document.querySelector('#sandBot');
        const totalMs = 150 * 86400000;

        if (!daysEl || !hoursEl || !minutesEl || !secondsEl) return;

        function plural(n, one, few, many) {
            const abs = Math.abs(n) % 100;
            const last = abs % 10;
            if (abs > 10 && abs < 20) return many;
            if (last === 1) return one;
            if (last > 1 && last < 5) return few;
            return many;
        }

        function updateSand(diff) {
            if (!sandTop || !sandBot) return;
            const p = Math.min(1, Math.max(0, diff / totalMs));
            const wave = Math.sin(Date.now() / 800) * 2;
            const topY = 44 + (1 - p) * 94;
            sandTop.setAttribute('d', p > 0.02 ? `M20,${topY} Q60,${topY - 4 + wave} 90,${topY + 2 + wave} Q120,${topY - 3 - wave} 160,${topY} L160,140 L20,140Z` : '');
            const botY = 236 - (1 - p) * 88;
            const bWave = Math.sin(Date.now() / 900 + 1) * 2;
            sandBot.setAttribute('d', p < 0.98 ? `M20,${botY} Q60,${botY - 3 + bWave} 90,${botY + 1 + bWave} Q120,${botY - 2 - bWave} 160,${botY} L160,240 L20,240Z` : '');
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
            if (labels[0]) labels[0].textContent = plural(days, 'день', 'дні', 'днів');
            if (labels[1]) labels[1].textContent = plural(hours, 'година', 'години', 'годин');
            if (labels[2]) labels[2].textContent = plural(minutes, 'хвилина', 'хвилини', 'хвилин');
            if (labels[3]) labels[3].textContent = plural(seconds, 'секунда', 'секунди', 'секунд');
            updateSand(diff);
        }

        tick();
        window.setInterval(tick, 1000);

        if (sandTop && sandBot) {
            const animateSand = () => {
                updateSand(Math.max(0, target - Date.now()));
                window.requestAnimationFrame(animateSand);
            };
            window.requestAnimationFrame(animateSand);
        }
    }

    function setupRsvp() {
        const rsvpForm = document.querySelector('.rsvp-form');
        if (!rsvpForm) return;

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

        function setCustomMessage(field, message) {
            if (!field) return;
            field.addEventListener('invalid', () => {
                if ((field.value || '').trim() === '') field.setCustomValidity(message);
            });
            field.addEventListener('change', () => field.setCustomValidity(''));
            field.addEventListener('input', () => field.setCustomValidity(''));
        }

        setCustomMessage(mainDrinkSelect, 'Будь ласка, оберіть свій напій.');
        setCustomMessage(partnerDrinkSelect, 'Будь ласка, оберіть напій для партнера.');
        setCustomMessage(plusOneNameInput, 'Будь ласка, вкажіть ім’я супутника.');

        function getAttendanceState() {
            const coupleSelected = document.querySelector('[data-couple-attendance]:checked');
            if (coupleSelected) {
                return {
                    hasAnswer: true,
                    willAttend: coupleSelected.value !== 'none',
                    primaryAttends: coupleSelected.value === 'both' || coupleSelected.value === 'primary',
                    partnerAttends: coupleSelected.value === 'both' || coupleSelected.value === 'partner',
                    isCouple: true,
                    value: coupleSelected.value
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
                isCouple: false,
                value: selected?.value || ''
            };
        }

        function syncRsvpVisibility() {
            const state = getAttendanceState();

            if (coupleWillAttend) coupleWillAttend.value = state.willAttend ? '1' : '0';
            if (couplePlusOne) couplePlusOne.value = state.partnerAttends ? '1' : '0';

            if (rsvpExtra) rsvpExtra.classList.toggle('is-hidden', !state.willAttend);

            if (mainDrinkSelect) {
                mainDrinkSelect.required = state.primaryAttends;
                if (!state.primaryAttends) mainDrinkSelect.value = '';
            }

            if (plusOneName) plusOneName.classList.toggle('is-hidden', !state.partnerAttends || state.isCouple);
            if (plusOneNameInput) plusOneNameInput.required = state.partnerAttends && !state.isCouple;

            if (partnerDrink && !partnerDrink.dataset.alwaysVisible) {
                partnerDrink.classList.toggle('is-hidden', !state.partnerAttends);
            }

            if (partnerDrinkSelect) {
                partnerDrinkSelect.required = state.partnerAttends;
                if (!state.partnerAttends) partnerDrinkSelect.value = '';
            }

            if (partnerDrinkName && plusOneNameInput && !partnerDrink?.dataset.alwaysVisible) {
                partnerDrinkName.textContent = plusOneNameInput.value.trim() || 'партнер / супутник';
            }

            if (rsvpSubmit) {
                rsvpSubmit.classList.toggle('is-hidden', !state.hasAnswer);
                rsvpSubmit.textContent = state.willAttend ? 'Підтвердити подорож' : 'Шкода, але життя вносить свої корективи';
            }
        }

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

        plusOneRadios.forEach((radio) => radio.addEventListener('change', syncRsvpVisibility));
        plusOneNameInput?.addEventListener('input', syncRsvpVisibility);

        rsvpForm.addEventListener('submit', (event) => {
            syncRsvpVisibility();
            if (!rsvpForm.checkValidity()) {
                event.preventDefault();
                rsvpForm.reportValidity();
            }
        });

        syncRsvpVisibility();
    }

    function setupCopyLink() {
        if (!copyLinkButton) return;

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

    function setupReveal() {
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
    }

    function setupRsvpModal() {
        const rsvpErrorModal = document.querySelector('[data-rsvp-error-modal]');
        const rsvpErrorCloseButtons = document.querySelectorAll('[data-close-rsvp-error]');
        rsvpErrorCloseButtons.forEach((button) => {
            button.addEventListener('click', () => rsvpErrorModal?.classList.remove('is-visible'));
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') rsvpErrorModal?.classList.remove('is-visible');
        });
    }

    safeRun(installInviteStyles);
    safeRun(ensureInviteSharedMoon);
    safeRun(setupMusic);
    safeRun(setupOpenInvite);
    safeRun(setupStarLayers);
    safeRun(setupIntegratedInviteIntro);
    safeRun(setupCountdown);
    safeRun(setupRsvp);
    safeRun(setupCopyLink);
    safeRun(setupReveal);
    safeRun(setupRsvpModal);

    if (document.querySelector('[data-confetti-on-load]')) {
        window.setTimeout(confetti, 450);
    }
})();
