const copyMessageButtons = document.querySelectorAll('[data-copy-message]');
const copyPhoneButtons = document.querySelectorAll('[data-copy-phone]');
const copySelectInputs = document.querySelectorAll('.copy-select-input');
const confirmDeleteForms = document.querySelectorAll('.confirm-delete-form');

copyMessageButtons.forEach((button) => {
    button.addEventListener('click', async () => {
        const message = button.dataset.copyMessage || '';
        const originalText = button.textContent;

        try {
            await navigator.clipboard.writeText(message);
            button.textContent = 'Скопійовано';
        } catch (error) {
            window.prompt('Скопіюйте повідомлення', message);
        }

        window.setTimeout(() => {
            button.textContent = originalText;
        }, 1800);
    });
});

copyPhoneButtons.forEach((button) => {
    button.addEventListener('click', async () => {
        const phone = button.dataset.copyPhone || '';
        const originalText = button.textContent;

        try {
            await navigator.clipboard.writeText(phone);
            button.textContent = 'Телефон скопійовано';
        } catch (error) {
            window.prompt('Скопіюйте телефон', phone);
        }

        window.setTimeout(() => {
            button.textContent = originalText;
        }, 1800);
    });
});

copySelectInputs.forEach((input) => {
    input.addEventListener('click', () => input.select());
});

confirmDeleteForms.forEach((form) => {
    form.addEventListener('submit', (event) => {
        const message = form.dataset.confirmMessage || 'Видалити запис?';

        if (!window.confirm(message)) {
            event.preventDefault();
        }
    });
});
