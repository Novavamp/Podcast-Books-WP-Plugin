(function () {
  'use strict';

  function initLibrary(library) {
    var form = library.querySelector('.jidoba-filters');
    var results = library.querySelector('.jidoba-results');
    var status = library.querySelector('.jidoba-status');
    var requestId = 0;
    var searchTimer;

    function load(page) {
      var currentRequest = ++requestId;
      var data = new URLSearchParams();
      var search = form ? form.querySelector('.jidoba-search') : null;
      var term = form ? form.querySelector('.jidoba-term') : null;

      data.append('action', 'jidoba_filter_media');
      data.append('nonce', jidobaMediaLibrary.nonce);
      data.append('type', library.dataset.type);
      data.append('per_page', library.dataset.perPage);
      data.append('page', page || 1);
      data.append('search', search ? search.value.trim() : '');
      data.append('term', library.dataset.fixedTerm || (term ? term.value : ''));

      library.classList.add('is-loading');
      library.setAttribute('aria-busy', 'true');
      status.textContent = '';

      fetch(jidobaMediaLibrary.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: data.toString()
      })
        .then(function (response) {
          if (!response.ok) throw new Error('Request failed');
          return response.json();
        })
        .then(function (response) {
          if (currentRequest !== requestId) return;
          if (!response.success) throw new Error('Invalid response');
          results.innerHTML = response.data.html;
          status.textContent = response.data.found + (response.data.found === 1 ? ' item found' : ' items found');
        })
        .catch(function () {
          if (currentRequest === requestId) status.textContent = jidobaMediaLibrary.errorText;
        })
        .finally(function () {
          if (currentRequest === requestId) {
            library.classList.remove('is-loading');
            library.removeAttribute('aria-busy');
          }
        });
    }

    if (form) {
      form.addEventListener('submit', function (event) {
        event.preventDefault();
        load(1);
      });

      var termSelect = form.querySelector('.jidoba-term');
      if (termSelect) termSelect.addEventListener('change', function () { load(1); });

      var searchInput = form.querySelector('.jidoba-search');
      if (searchInput) {
        searchInput.addEventListener('input', function () {
          window.clearTimeout(searchTimer);
          searchTimer = window.setTimeout(function () { load(1); }, 350);
        });
      }
    }

    library.addEventListener('click', function (event) {
      var button = event.target.closest('.jidoba-pagination button');
      if (!button || !library.contains(button)) return;
      load(parseInt(button.dataset.page, 10) || 1);
      library.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  }

  function init() {
    document.querySelectorAll('.jidoba-library').forEach(initLibrary);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
