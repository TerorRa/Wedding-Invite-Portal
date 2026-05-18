document.documentElement.classList.add('js-ready');
document.documentElement.classList.remove('no-js');

const plusOneRadios = document.querySelectorAll('input[name="plus_one"]');
const plusOneName = document.querySelector('.plus-one-name');

function syncPlusOneName() {
    if (!plusOneName) {
        return;
    }

    const selected = document.querySelector('input[name="plus_one"]:checked');
    plusOneName.classList.toggle('is-hidden', selected?.value !== '1');
}

plusOneRadios.forEach((radio) => {
    radio.addEventListener('change', syncPlusOneName);
});

syncPlusOneName();

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
