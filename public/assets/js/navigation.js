document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('[data-navigation-sidebar]');
    if (!sidebar) {
        return;
    }

    const shell = sidebar.closest('.dashboard-shell, .admin-shell');
    const storageKey = 'pccurico-navigation';
    let stored = {};
    try {
        stored = JSON.parse(localStorage.getItem(storageKey) || '{}') || {};
    } catch {
        localStorage.removeItem(storageKey);
    }
    const groups = [...sidebar.querySelectorAll('[data-navigation-group]')];

    if (stored.collapsed && shell) {
        shell.classList.add('sidebar-collapsed');
    }

    groups.forEach((group) => {
        const toggle = group.querySelector('[data-navigation-toggle]');
        group.classList.remove('is-open');
        toggle?.setAttribute('aria-expanded', 'false');
        toggle?.addEventListener('click', () => {
            const open = !group.classList.contains('is-open');
            groups.forEach((otherGroup) => {
                otherGroup.classList.toggle('is-open', open && otherGroup === group);
                otherGroup.querySelector('[data-navigation-toggle]')?.setAttribute('aria-expanded', open && otherGroup === group ? 'true' : 'false');
            });
        });
    });

    sidebar.closest('.dashboard-shell, .admin-shell')?.querySelectorAll('[data-navigation-collapse]').forEach((collapseButton) => {
        collapseButton.addEventListener('click', () => {
            if (!shell) {
                return;
            }
            const collapsed = shell.classList.toggle('sidebar-collapsed');
            stored.collapsed = collapsed;
            localStorage.setItem(storageKey, JSON.stringify(stored));
        });
    });

    sidebar.querySelector('[data-navigation-search]')?.addEventListener('input', (event) => {
        const term = event.target.value.trim().toLowerCase();
        let firstVisibleGroup = null;
        groups.forEach((group) => {
            const items = [...group.querySelectorAll('[data-navigation-item]')];
            let visible = 0;
            const groupMatches = term !== '' && (group.dataset.searchLabel || '').includes(term);
            items.forEach((item) => {
                const matches = term === '' || groupMatches || item.dataset.searchLabel.includes(term);
                item.hidden = !matches;
                visible += matches ? 1 : 0;
            });
            group.hidden = visible === 0;
            if (term !== '' && visible > 0 && firstVisibleGroup === null) {
                firstVisibleGroup = group;
            }
        });
        groups.forEach((group) => {
            const open = firstVisibleGroup === group;
            group.classList.toggle('is-open', open);
            group.querySelector('[data-navigation-toggle]')?.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });

    sidebar.closest('.dashboard-shell, .admin-shell')?.querySelector('[data-navigation-global-search]')?.addEventListener('input', (event) => {
        const sidebarSearch = sidebar.querySelector('[data-navigation-search]');
        if (!sidebarSearch) {
            return;
        }
        sidebarSearch.value = event.target.value;
        sidebarSearch.dispatchEvent(new Event('input', { bubbles: true }));
    });

    document.querySelector('[data-dashboard-period-filter]')?.addEventListener('change', (event) => {
        if (event.target.matches('input[type="date"], select')) {
            event.currentTarget.requestSubmit();
        }
    });
    document.querySelectorAll('[data-dashboard-period-filter] [data-dashboard-period]').forEach((button) => {
        button.addEventListener('click', () => {
            const form = button.closest('[data-dashboard-period-filter]');
            const periodInput = form?.querySelector('[data-dashboard-period-value]');
            if (periodInput && form) {
                periodInput.value = button.dataset.dashboardPeriod;
                form.requestSubmit();
            }
        });
    });

    const dashboardCalendar = document.querySelector('[data-dashboard-calendar]');
    if (dashboardCalendar) {
        const activityDates = new Set(JSON.parse(dashboardCalendar.dataset.activityDates || '[]'));
        const fromInput = document.querySelector('[data-dashboard-date-from]');
        const toInput = document.querySelector('[data-dashboard-date-to]');
        const daysContainer = dashboardCalendar.querySelector('[data-calendar-days]');
        const monthLabel = dashboardCalendar.querySelector('[data-calendar-label]');
        let visibleMonth = new Date(`${(fromInput?.value || new Date().toISOString().slice(0, 10)).slice(0, 7)}-01T12:00:00`);
        const pad = (value) => String(value).padStart(2, '0');
        const dateKey = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
        const renderCalendar = () => {
            const year = visibleMonth.getFullYear();
            const month = visibleMonth.getMonth();
            const firstDay = (new Date(year, month, 1).getDay() + 6) % 7;
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            monthLabel.textContent = visibleMonth.toLocaleDateString('es-CL', { month: 'long', year: 'numeric' });
            daysContainer.replaceChildren();
            for (let index = 0; index < firstDay; index += 1) daysContainer.append(document.createElement('span'));
            for (let day = 1; day <= daysInMonth; day += 1) {
                const date = new Date(year, month, day);
                const key = dateKey(date);
                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = day;
                button.className = activityDates.has(key) ? 'has-activity' : '';
                if (key === fromInput?.value || key === toInput?.value) button.classList.add('selected');
                button.title = activityDates.has(key) ? 'Fecha con información' : 'Seleccionar fecha';
                button.addEventListener('click', () => {
                    if (fromInput && toInput && (!fromInput.value || (fromInput.value && toInput.value && fromInput.value !== toInput.value))) {
                        fromInput.value = key;
                        toInput.value = key;
                    } else if (fromInput && toInput) {
                        if (key < fromInput.value) { toInput.value = fromInput.value; fromInput.value = key; } else { toInput.value = key; }
                    }
                    fromInput?.form?.requestSubmit();
                });
                daysContainer.append(button);
            }
        };
        dashboardCalendar.querySelector('[data-calendar-previous]')?.addEventListener('click', () => { visibleMonth.setMonth(visibleMonth.getMonth() - 1); renderCalendar(); });
        dashboardCalendar.querySelector('[data-calendar-next]')?.addEventListener('click', () => { visibleMonth.setMonth(visibleMonth.getMonth() + 1); renderCalendar(); });
        renderCalendar();
    }

    sidebar.querySelectorAll('[data-navigation-favorite]').forEach((favorite) => {
        const favoriteId = favorite.dataset.favoriteId;
        const favorites = new Set(stored.favorites || []);
        favorite.classList.toggle('is-favorite', favorites.has(favoriteId));
        const toggleFavorite = (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (favorites.has(favoriteId)) {
                favorites.delete(favoriteId);
            } else {
                favorites.add(favoriteId);
            }
            favorite.classList.toggle('is-favorite', favorites.has(favoriteId));
            stored.favorites = [...favorites];
            localStorage.setItem(storageKey, JSON.stringify(stored));
        };
        favorite.addEventListener('click', toggleFavorite);
        favorite.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                toggleFavorite(event);
            }
        });
    });
});
