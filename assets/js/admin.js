const copyMessageButtons = document.querySelectorAll('[data-copy-message]');
const copyPhoneButtons = document.querySelectorAll('[data-copy-phone]');

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
