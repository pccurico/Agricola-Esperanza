/* Makes every data table readable as record cards on narrow screens. */
(() => {
    'use strict';
    document.querySelectorAll('table').forEach(table => {
        // Skip tables in inventory module
        if (table.closest('.inventory-v2')) return;

        const headings = [...table.querySelectorAll('thead th')].map(cell => cell.textContent.trim());
        if (!headings.length) return;
        table.classList.add('responsive-data-table');
        table.querySelectorAll('tbody tr').forEach(row => {
            [...row.children].forEach((cell, index) => {
                if (cell.tagName === 'TD' && headings[index]) cell.dataset.label = headings[index];
            });
        });
    });
})();
