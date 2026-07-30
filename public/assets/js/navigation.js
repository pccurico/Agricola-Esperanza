document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('[data-navigation-sidebar]');
    if (!sidebar) {
        return;
    }

    const shell = sidebar.closest('.dashboard-shell, .admin-shell');
    const storageKey = 'camposur-navigation';
    const stored = JSON.parse(localStorage.getItem(storageKey) || '{}');
    const groups = [...sidebar.querySelectorAll('[data-navigation-group]')];

    if (stored.collapsed && shell) {
        shell.classList.add('sidebar-collapsed');
    }

    groups.forEach((group) => {
        const id = group.dataset.groupId;
        const toggle = group.querySelector('[data-navigation-toggle]');
        const isActive = group.classList.contains('is-open');
        const shouldOpen = stored.groups?.[id] ?? isActive;
        group.classList.toggle('is-open', shouldOpen);
        toggle?.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        toggle?.addEventListener('click', () => {
            const open = group.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            stored.groups = { ...(stored.groups || {}), [id]: open };
            localStorage.setItem(storageKey, JSON.stringify(stored));
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
            if (term !== '' && visible > 0) {
                group.classList.add('is-open');
                group.querySelector('[data-navigation-toggle]')?.setAttribute('aria-expanded', 'true');
            }
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
