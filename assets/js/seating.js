(() => {
    const board = document.querySelector('[data-seating-board]');
    const searchInput = document.querySelector('[data-seating-search]');
    const printButton = document.querySelector('[data-print-seating]');
    const drinkStatsUrl = 'seating_drinks.php';

    if (!document.querySelector('link[data-seating-drinks-styles]')) {
        const styles = document.createElement('link');
        styles.rel = 'stylesheet';
        styles.href = '../assets/css/seating-drinks.css';
        styles.dataset.seatingDrinksStyles = '1';
        document.head.appendChild(styles);
    }

    const peopleLabel = (count) => {
        const lastTwo = count % 100;
        const last = count % 10;

        if (lastTwo >= 11 && lastTwo <= 14) {
            return `${count} осіб`;
        }

        if (last === 1) {
            return `${count} особа`;
        }

        if (last >= 2 && last <= 4) {
            return `${count} особи`;
        }

        return `${count} осіб`;
    };

    const zones = board ? Array.from(board.querySelectorAll('[data-table-zone]')) : [];
    const cards = board ? Array.from(board.querySelectorAll('[data-seating-card]')) : [];
    const cardsById = new Map(cards.map((card) => [card.dataset.guestId, card]));
    let draggedCard = null;

    const findZone = (tableNumber) => zones.find((zone) => zone.dataset.tableNumber === tableNumber) || null;

    const ensureDrinkContainers = () => {
        zones.forEach((zone) => {
            if (zone.dataset.tableNumber === '') {
                return;
            }

            const surface = zone.querySelector('.hall-table__surface');

            if (!surface || surface.querySelector('[data-table-drinks]')) {
                return;
            }

            const title = document.createElement('small');
            title.className = 'hall-table__drinks-title';
            title.textContent = 'Напої';

            const drinks = document.createElement('div');
            drinks.className = 'hall-table__drinks';
            drinks.dataset.tableDrinks = '1';

            const loading = document.createElement('span');
            loading.className = 'hall-table__drinks-empty';
            loading.textContent = 'Завантаження…';
            drinks.appendChild(loading);

            surface.append(title, drinks);
        });
    };

    const renderDrinkStats = (tables) => {
        zones.forEach((zone) => {
            const tableNumber = zone.dataset.tableNumber || '';
            const container = zone.querySelector('[data-table-drinks]');

            if (!container || tableNumber === '') {
                return;
            }

            const drinks = tables && typeof tables[tableNumber] === 'object' ? tables[tableNumber] : {};
            const entries = Object.entries(drinks).sort((left, right) => {
                const countDifference = Number(right[1]) - Number(left[1]);
                return countDifference !== 0 ? countDifference : left[0].localeCompare(right[0], 'uk-UA');
            });

            container.replaceChildren();

            if (entries.length === 0) {
                const empty = document.createElement('span');
                empty.className = 'hall-table__drinks-empty';
                empty.textContent = 'Не вказано';
                container.appendChild(empty);
                return;
            }

            entries.forEach(([drinkName, drinkCount]) => {
                const item = document.createElement('span');
                const name = document.createElement('b');
                const count = document.createElement('em');
                name.textContent = drinkName;
                count.textContent = String(drinkCount);
                item.append(name, count);
                container.appendChild(item);
            });
        });
    };

    const refreshDrinkStats = async () => {
        try {
            const response = await fetch(drinkStatsUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                cache: 'no-store',
            });
            const payload = await response.json();

            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Не вдалося завантажити статистику напоїв.');
            }

            renderDrinkStats(payload.tables || {});
        } catch (error) {
            zones.forEach((zone) => {
                const container = zone.querySelector('[data-table-drinks]');

                if (!container) {
                    return;
                }

                container.replaceChildren();
                const message = document.createElement('span');
                message.className = 'hall-table__drinks-empty';
                message.textContent = 'Помилка';
                container.appendChild(message);
            });
        }
    };

    const refreshCounts = () => {
        let seatedPeople = 0;
        let unseatedPeople = 0;

        zones.forEach((zone) => {
            const zoneCards = Array.from(zone.querySelectorAll('[data-seating-card]'));
            const visibleCards = zoneCards.filter((card) => !card.hidden);
            const people = zoneCards.reduce((sum, card) => sum + Number(card.dataset.personCount || 0), 0);
            const counter = zone.querySelector('[data-table-count]');
            const emptyMessage = zone.querySelector('[data-table-empty]');

            if (counter) {
                counter.textContent = peopleLabel(people);
            }

            if (emptyMessage) {
                emptyMessage.hidden = visibleCards.length > 0;
            }

            if (zone.dataset.tableNumber === '') {
                unseatedPeople += people;
            } else {
                seatedPeople += people;
            }
        });

        const seatedCounter = document.querySelector('[data-seated-people]');
        const unseatedCounter = document.querySelector('[data-unseated-people]');

        if (seatedCounter) {
            seatedCounter.textContent = String(seatedPeople);
        }

        if (unseatedCounter) {
            unseatedCounter.textContent = String(unseatedPeople);
        }
    };

    const moveCard = (card, tableNumber) => {
        const zone = findZone(tableNumber);
        const list = zone ? zone.querySelector('[data-table-list]') : null;

        if (!list) {
            return;
        }

        list.appendChild(card);
        card.dataset.tableNumber = tableNumber;

        const select = card.querySelector('[data-seating-select]');
        if (select) {
            select.value = tableNumber;
        }

        refreshCounts();
    };

    const setStatus = (card, message, state = '') => {
        const status = card.querySelector('[data-save-status]');

        if (!status) {
            return;
        }

        status.textContent = message;
        status.dataset.state = state;
    };

    const saveCard = async (card, tableNumber) => {
        if (card.dataset.saving === '1') {
            return false;
        }

        const previousTableNumber = card.dataset.tableNumber || '';
        const formData = new FormData(card);
        formData.set('table_number', tableNumber);
        card.dataset.saving = '1';
        card.classList.add('is-saving');
        setStatus(card, 'Збереження…', 'saving');

        try {
            const response = await fetch(card.action, {
                method: 'POST',
                body: formData,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const payload = await response.json();

            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Не вдалося зберегти розсадку.');
            }

            moveCard(card, String(payload.table_number ?? tableNumber));
            await refreshDrinkStats();
            setStatus(card, 'Збережено', 'success');
            window.setTimeout(() => setStatus(card, '', ''), 1800);
            return true;
        } catch (error) {
            const select = card.querySelector('[data-seating-select]');
            if (select) {
                select.value = previousTableNumber;
            }
            setStatus(card, error instanceof Error ? error.message : 'Не вдалося зберегти розсадку.', 'error');
            return false;
        } finally {
            card.dataset.saving = '0';
            card.classList.remove('is-saving');
        }
    };

    cards.forEach((card) => {
        const select = card.querySelector('[data-seating-select]');

        card.addEventListener('submit', (event) => {
            event.preventDefault();
            saveCard(card, select ? select.value : '');
        });

        if (select) {
            select.addEventListener('change', () => saveCard(card, select.value));
            select.addEventListener('pointerdown', (event) => event.stopPropagation());
            select.addEventListener('dragstart', (event) => event.preventDefault());
        }

        card.addEventListener('dragstart', (event) => {
            if (event.target instanceof HTMLElement && event.target.closest('select, button, input, a')) {
                event.preventDefault();
                return;
            }

            draggedCard = card;
            card.classList.add('is-dragging');
            event.dataTransfer?.setData('text/plain', card.dataset.guestId || '');
            if (event.dataTransfer) {
                event.dataTransfer.effectAllowed = 'move';
            }
        });

        card.addEventListener('dragend', () => {
            draggedCard = null;
            card.classList.remove('is-dragging');
            zones.forEach((zone) => zone.classList.remove('is-drag-over'));
        });
    });

    zones.forEach((zone) => {
        zone.addEventListener('dragover', (event) => {
            event.preventDefault();
            zone.classList.add('is-drag-over');
            if (event.dataTransfer) {
                event.dataTransfer.dropEffect = 'move';
            }
        });

        zone.addEventListener('dragleave', (event) => {
            if (!zone.contains(event.relatedTarget)) {
                zone.classList.remove('is-drag-over');
            }
        });

        zone.addEventListener('drop', (event) => {
            event.preventDefault();
            zone.classList.remove('is-drag-over');

            const guestId = event.dataTransfer?.getData('text/plain') || '';
            const card = draggedCard || cardsById.get(guestId);
            const tableNumber = zone.dataset.tableNumber || '';

            if (!card || card.dataset.tableNumber === tableNumber) {
                return;
            }

            saveCard(card, tableNumber);
        });
    });

    if (searchInput && board) {
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLocaleLowerCase('uk-UA');

            cards.forEach((card) => {
                const haystack = (card.dataset.guestSearch || '').toLocaleLowerCase('uk-UA');
                card.hidden = query !== '' && !haystack.includes(query);
            });

            refreshCounts();
        });
    }

    printButton?.addEventListener('click', () => window.print());
    ensureDrinkContainers();
    refreshCounts();
    refreshDrinkStats();
})();
