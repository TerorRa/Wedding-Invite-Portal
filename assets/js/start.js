document.documentElement.classList.remove('no-js');

const scene = document.querySelector('[data-intro-scene]');
const finalBlock = document.querySelector('[data-intro-final]');
const skipIntroButton = document.querySelector('[data-skip-intro]');
const openStartButton = document.querySelector('[data-open-start]');
const shineSound = document.querySelector('[data-shine-sound]');

document.body.classList.add('force-intro-motion');

function buildStartStars(container, count) {
    if (!container) {
        return;
    }

    for (let i = 0; i < count; i++) {
        const star = document.createElement('i');
        star.style.left = `${Math.random() * 100}%`;
        star.style.top = `${Math.random() * 100}%`;
        star.style.setProperty('--size', `${1 + Math.random() * 2.4}px`);
        star.style.setProperty('--alpha', `${0.48 + Math.random() * 0.44}`);
        star.style.setProperty('--duration', `${3.8 + Math.random() * 4.8}s`);
        star.style.setProperty('--delay', `${Math.random() * 5}s`);
        container.appendChild(star);
    }
}

function buildStartStarbursts(container, count) {
    if (!container) {
        return;
    }

    for (let i = 0; i < count; i++) {
        const star = document.createElement('span');
        star.className = 'start-star4';
        star.style.left = `${8 + Math.random() * 84}%`;
        star.style.top = `${5 + Math.random() * 74}%`;
        star.style.setProperty('--size', `${10 + Math.random() * 9}px`);
        star.style.setProperty('--alpha', `${0.56 + Math.random() * 0.34}`);
        star.style.setProperty('--duration', `${3.6 + Math.random() * 3.2}s`);
        star.style.setProperty('--delay', `${Math.random() * 5.5}s`);
        container.appendChild(star);
    }
}

function buildStartComets(container, count) {
    if (!container) {
        return;
    }

    for (let i = 0; i < count; i++) {
        const comet = document.createElement('span');
        comet.className = 'start-comet';
        comet.style.left = `${-12 - Math.random() * 18}vw`;
        comet.style.top = `${8 + Math.random() * 78}%`;
        comet.style.setProperty('--tail', `${90 + Math.random() * 130}px`);
        comet.style.setProperty('--angle', `${20 + Math.random() * 16}deg`);
        comet.style.setProperty('--drop', `${16 + Math.random() * 36}vh`);
        comet.style.setProperty('--duration', `${6 + Math.random() * 5.5}s`);
        comet.style.setProperty('--delay', `${i * 3.2 + Math.random() * 5}s`);
        container.appendChild(comet);
    }
}

buildStartStars(document.querySelector('.start-starfield'), 38);
buildStartStarbursts(document.querySelector('.start-starbursts'), 7);
buildStartComets(document.querySelector('.start-comets'), 5);

if (scene) {
    const introDuration = Number.parseInt(scene.dataset.introDuration || '7600', 10);
    const completionDelay = Number.isFinite(introDuration) ? introDuration : 7600;
    const completeIntro = () => {
        scene.classList.add('is-complete', 'is-skip-hidden');
    };

    window.setTimeout(completeIntro, completionDelay);

    skipIntroButton?.addEventListener('click', completeIntro);
}

if (finalBlock) {
    const firstButton = finalBlock.querySelector('a');
    const introDuration = scene ? Number.parseInt(scene.dataset.introDuration || '7600', 10) : 7600;

    window.setTimeout(() => {
        firstButton?.focus({ preventScroll: true });
    }, (Number.isFinite(introDuration) ? introDuration : 7600) + 400);
}

if (openStartButton && scene && finalBlock) {
    const playShine = (volume = 0.35) => {
        if (!shineSound) {
            return;
        }

        try {
            shineSound.currentTime = 0;
            shineSound.volume = volume;
        } catch (error) { }

        shineSound.play().catch(() => { });
    };

    const setHover = (isHovered) => {
        finalBlock.classList.toggle('is-open-hover', isHovered);
    };

    openStartButton.addEventListener('mouseenter', () => {
        setHover(true);
        playShine(0.18);
    });
    openStartButton.addEventListener('mouseleave', () => setHover(false));
    openStartButton.addEventListener('focus', () => {
        setHover(true);
        playShine(0.18);
    });
    openStartButton.addEventListener('blur', () => setHover(false));

    openStartButton.addEventListener('click', (event) => {
        event.preventDefault();

        if (openStartButton.dataset.opening === '1') {
            return;
        }

        openStartButton.dataset.opening = '1';
        playShine(0.75);
        scene.classList.add('is-shine-burst');
        finalBlock.classList.add('is-shining');

        window.setTimeout(() => {
            window.location.href = openStartButton.href;
        }, 1120);
    });
}
