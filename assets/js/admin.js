const copyMessageButtons = document.querySelectorAll('[data-copy-message]');
const copyPhoneButtons = document.querySelectorAll('[data-copy-phone]');
const copySelectInputs = document.querySelectorAll('.copy-select-input');
const confirmDeleteForms = document.querySelectorAll('.confirm-delete-form');
const confirmGuestModal = document.querySelector('[data-confirm-guest-modal]');
const confirmGuestButtons = document.querySelectorAll('[data-confirm-guest]');
const confirmGuestCloseButtons = document.querySelectorAll('[data-confirm-guest-close]');
const confirmGuestForm = confirmGuestModal?.querySelector('.admin-confirm-form');
const confirmGuestId = confirmGuestModal?.querySelector('[data-confirm-guest-id]');
const confirmGuestName = confirmGuestModal?.querySelector('[data-confirm-guest-name]');
const confirmGuestDrink = confirmGuestModal?.querySelector('[data-confirm-drink]');
const confirmGuestPlusOne = confirmGuestModal?.querySelector('[data-confirm-plus-one]');
const confirmGuestPlusOneToggle = confirmGuestModal?.querySelector('[data-confirm-plus-one-toggle]');
const confirmGuestPlusOneFields = confirmGuestModal?.querySelector('[data-confirm-plus-one-fields]');
const confirmGuestPlusOneName = confirmGuestModal?.querySelector('[data-confirm-plus-one-name]');
const confirmGuestPartnerDrink = confirmGuestModal?.querySelector('[data-confirm-partner-drink]');

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

function setSelectValue(select, value) {
    if (!select) {
        return;
    }

    select.value = value || '';

    if (value && select.value !== value) {
        select.value = '';
    }
}

function syncConfirmGuestPlusOne() {
    if (!confirmGuestPlusOne || !confirmGuestPlusOneFields) {
        return;
    }

    const isEnabled = confirmGuestPlusOne.checked && !confirmGuestPlusOne.disabled;

    confirmGuestPlusOneFields.classList.toggle('is-hidden', !isEnabled);

    if (confirmGuestPlusOneName) {
        confirmGuestPlusOneName.required = isEnabled;

        if (!isEnabled) {
            confirmGuestPlusOneName.value = '';
        }
    }

    if (confirmGuestPartnerDrink) {
        confirmGuestPartnerDrink.required = isEnabled;

        if (!isEnabled) {
            confirmGuestPartnerDrink.value = '';
        }
    }
}

function closeConfirmGuestModal() {
    confirmGuestModal?.classList.remove('is-visible');
    confirmGuestModal?.setAttribute('aria-hidden', 'true');
}

confirmGuestButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const allowsPlusOne = button.dataset.allowsPlusOne === '1';
        const hasPlusOne = button.dataset.plusOne === '1';

        confirmGuestForm?.reset();

        if (confirmGuestId) {
            confirmGuestId.value = button.dataset.guestId || '';
        }

        if (confirmGuestName) {
            confirmGuestName.textContent = button.dataset.guestName || '';
        }

        setSelectValue(confirmGuestDrink, button.dataset.drink || '');

        if (confirmGuestPlusOne) {
            confirmGuestPlusOne.disabled = !allowsPlusOne;
            confirmGuestPlusOne.checked = allowsPlusOne && hasPlusOne;
        }

        if (confirmGuestPlusOneToggle) {
            confirmGuestPlusOneToggle.classList.toggle('is-hidden', !allowsPlusOne);
        }

        if (confirmGuestPlusOneName) {
            confirmGuestPlusOneName.value = allowsPlusOne ? (button.dataset.plusOneName || '') : '';
        }

        setSelectValue(confirmGuestPartnerDrink, allowsPlusOne ? (button.dataset.partnerDrink || '') : '');

        syncConfirmGuestPlusOne();
        confirmGuestModal?.classList.add('is-visible');
        confirmGuestModal?.setAttribute('aria-hidden', 'false');
        confirmGuestDrink?.focus();
    });
});

confirmGuestPlusOne?.addEventListener('change', syncConfirmGuestPlusOne);

confirmGuestCloseButtons.forEach((button) => {
    button.addEventListener('click', closeConfirmGuestModal);
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeConfirmGuestModal();
    }
});
