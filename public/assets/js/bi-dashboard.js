document.addEventListener('DOMContentLoaded', () => {
  // Auto-submit filters
  document.querySelectorAll('[data-bi-filter] input, [data-bi-filter] select').forEach(el => el.addEventListener('change', () => el.closest('form')?.requestSubmit()));

  // Simple drag & drop ordering for widgets
  const container = document.querySelector('[data-bi-widgets]');
  if (container) {
    container.querySelectorAll('[data-bi-widget]').forEach(node => {
      node.draggable = true;
      node.addEventListener('dragstart', (e) => { node.classList.add('dragging'); e.dataTransfer?.setData('text/plain', node.dataset.biWidget || ''); });
      node.addEventListener('dragend', () => node.classList.remove('dragging'));
    });
    container.addEventListener('dragover', (e) => {
      e.preventDefault();
      const after = getDragAfterElement(container, e.clientY);
      const dragging = container.querySelector('.dragging');
      if (!dragging) return;
      if (after == null) container.appendChild(dragging); else container.insertBefore(dragging, after);
    });
  }

  function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('[data-bi-widget]:not(.dragging)')];
    return draggableElements.reduce((closest, child) => {
      const box = child.getBoundingClientRect();
      const offset = y - box.top - box.height / 2;
      if (offset < 0 && offset > (closest?.offset || -Infinity)) {
        return {offset, element: child};
      } else return closest;
    }, null)?.element || null;
  }

  // Save view: collect widget order and visible widget ids
  const saveBtn = document.querySelector('[data-bi-save]');
  if (saveBtn) saveBtn.addEventListener('click', (e) => {
    e.preventDefault();
    const form = document.querySelector('[data-bi-save-form]');
    if (!form) return;
    const widgets = [...document.querySelectorAll('[data-bi-widgets] [data-bi-widget]')].map(n => n.dataset.biWidget || '').filter(Boolean);
    // remove existing hidden inputs
    form.querySelectorAll('input[name="widgets[]"]').forEach(i => i.remove());
    widgets.forEach(id => {
      const input = document.createElement('input'); input.type = 'hidden'; input.name = 'widgets[]'; input.value = id; form.appendChild(input);
    });
    form.requestSubmit();
  });

  // Minimal charts initialization using canvas data provided inline
  document.querySelectorAll('[data-bi-chart]').forEach(canvas => {
    try {
      const data = JSON.parse(canvas.dataset.biChart || '[]');
      const type = canvas.dataset.biChartType || 'line';
      // load Chart.js if available
      if (window.Chart) new Chart(canvas.getContext('2d'), {type, data: {labels: data.labels || [], datasets: data.datasets || []}, options:{responsive:true,maintainAspectRatio:false}});
    } catch (e) { /* ignore */ }
  });
});

// expose helper for potential UI
window.biDashboard = { version: '0.1' };
