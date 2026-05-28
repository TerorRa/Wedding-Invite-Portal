document.documentElement.classList.remove('no-js');

const scene = document.querySelector('[data-intro-scene]');
const finalBlock = document.querySelector('[data-intro-final]');

if (scene) {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const introDuration = Number.parseInt(scene.dataset.introDuration || '7600', 10);

    if (prefersReducedMotion) {
        scene.classList.add('is-complete');
    } else {
        window.setTimeout(() => {
            scene.classList.add('is-complete');
        }, Number.isFinite(introDuration) ? introDuration : 7600);
    }
}

if (finalBlock) {
    const firstButton = finalBlock.querySelector('a');
    const introDuration = scene ? Number.parseInt(scene.dataset.introDuration || '7600', 10) : 7600;

    window.setTimeout(() => {
        firstButton?.focus({ preventScroll: true });
    }, (Number.isFinite(introDuration) ? introDuration : 7600) + 400);
}
