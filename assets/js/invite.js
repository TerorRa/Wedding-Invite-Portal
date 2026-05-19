document.documentElement.classList.add('js-ready');
document.documentElement.classList.remove('no-js');

const openInviteButton = document.querySelector('[data-open-invite]');
const inviteContent = document.querySelector('#inviteContent');
const copyLinkButton = document.querySelector('[data-copy-link]');

if (openInviteButton && inviteContent) {
    openInviteButton.addEventListener('click', () => {
        document.body.classList.add('invite-opened');

        window.setTimeout(() => {
            inviteContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 260);
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
