document.addEventListener('DOMContentLoaded', () => {
    const search = document.querySelector('.reception-search');
    const cards = [...document.querySelectorAll('[data-reception-card]')];
    if (!search || cards.length === 0) {
        return;
    }

    search.addEventListener('input', () => {
        const term = search.value.trim().toLowerCase();
        cards.forEach((card) => {
            card.hidden = term !== '' && !card.dataset.searchText.includes(term);
        });
    });
});
