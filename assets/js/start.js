document.documentElement.classList.remove('no-js');

const scene = document.querySelector('[data-intro-scene]');
const finalBlock = document.querySelector('[data-intro-final]');

if (scene) {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) {
        scene.classList.add('is-complete');
    } else {
        window.setTimeout(() => {
            scene.classList.add('is-complete');
        }, 7200);
    }
}

if (finalBlock) {
    const firstButton = finalBlock.querySelector('a');

    window.setTimeout(() => {
        firstButton?.focus({ preventScroll: true });
    }, 7600);
}
