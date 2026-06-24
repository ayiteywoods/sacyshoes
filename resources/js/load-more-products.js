function initLoadMoreProducts() {
  document.querySelectorAll('[data-load-more]').forEach((button) => {
    if (button.dataset.loadMoreBound === 'true') {
      return;
    }

    button.dataset.loadMoreBound = 'true';

    button.addEventListener('click', async () => {
      const target = document.querySelector(button.dataset.target);
      const url = new URL(button.dataset.url, window.location.origin);

      url.searchParams.set('page', button.dataset.page);

      const label = button.textContent.trim();

      button.disabled = true;
      button.textContent = 'Loading...';

      try {
        const response = await fetch(url, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
          },
        });

        if (!response.ok) {
          throw new Error('Request failed');
        }

        const data = await response.json();

        const fragment = document.createRange().createContextualFragment(data.html);
        const appendedNodes = [...fragment.children];

        target?.append(...appendedNodes);

        if (window.Alpine && appendedNodes.length > 0) {
          window.Alpine.initTree(target);
        }

        if (data.has_more) {
          button.dataset.page = String(data.next_page);
          button.disabled = false;
          button.textContent = label;
        } else {
          button.closest('[data-load-more-wrapper]')?.remove();
        }
      } catch {
        button.disabled = false;
        button.textContent = label;
      }
    });
  });
}

document.addEventListener('DOMContentLoaded', initLoadMoreProducts);

export { initLoadMoreProducts };
