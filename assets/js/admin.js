(function () {
  'use strict';

  function fmtDate(ts) {
    if (!ts) return '-';
    var d = new Date(Number(ts));
    return isNaN(d.getTime()) ? '-' : d.toLocaleDateString();
  }

  function fmtDateTime(ts) {
    if (!ts) return '-';
    var d = new Date(Number(ts));
    return isNaN(d.getTime()) ? '-' : d.toLocaleString();
  }

  function deny(message) {
    var content = document.querySelector('.bxm-admin-content');
    if (content) {
      content.innerHTML = '<div class="bxm-state-box text-center"><i class="bi bi-shield-lock bxm-state-icon"></i><h5>Access Denied</h5><p class="text-secondary mb-3">' + window.BXM.escapeHtml(message || 'You are not authorized.') + '</p><a href="' + window.BXM.url('index.php') + '" class="bxm-btn bxm-btn-primary">Back to Home</a></div>';
    }
  }

  function phpIsAdmin() {
    return !!(window.BXM_APP && window.BXM_APP.role === 'admin');
  }

  function ready(cb) {
    function run() {
      if (!window.BXM || typeof window.BXM.onAuth !== 'function') {
        setTimeout(run, 40);
        return;
      }
      window.BXM.onAuth(function (user) {
        if (!window.BXM.firebaseReady) {
          deny('Firebase is not configured yet. Set your Firebase Web API key in includes/config.php.');
          return;
        }
        if (window.BXM.isAdmin || phpIsAdmin()) {
          cb(user);
          return;
        }
        if (!user) {
          window.location.replace(window.BXM.url('admin/login.php'));
          return;
        }
        var n = 0;
        var t = setInterval(function () {
          n += 1;
          if (window.BXM.isAdmin || phpIsAdmin()) {
            clearInterval(t);
            cb(user);
            return;
          }
          if (n > 40) {
            clearInterval(t);
            window.location.replace(window.BXM.url('admin/login.php'));
          }
        }, 50);
      });
    }
    run();
  }

  function pageSlice(items, page, perPage) {
    var total = items.length;
    var pages = Math.max(1, Math.ceil(total / perPage));
    page = Math.min(Math.max(1, page), pages);
    var start = (page - 1) * perPage;
    return { items: items.slice(start, start + perPage), page: page, pages: pages, total: total };
  }

  function renderPager(container, page, pages, onPage) {
    var host = typeof container === 'string' ? document.getElementById(container) : container;
    if (!host) return;
    if (pages <= 1) { host.innerHTML = ''; return; }
    var html = '<button class="bxm-btn bxm-btn-outline bxm-btn-sm" data-page="' + (page - 1) + '"' + (page <= 1 ? ' disabled' : '') + '><i class="bi bi-chevron-left"></i></button>';
    var start = Math.max(1, page - 2);
    var end = Math.min(pages, start + 4);
    start = Math.max(1, end - 4);
    for (var i = start; i <= end; i++) {
      html += '<button class="bxm-btn bxm-btn-sm ' + (i === page ? 'bxm-btn-primary' : 'bxm-btn-outline') + '" data-page="' + i + '">' + i + '</button>';
    }
    html += '<button class="bxm-btn bxm-btn-outline bxm-btn-sm" data-page="' + (page + 1) + '"' + (page >= pages ? ' disabled' : '') + '><i class="bi bi-chevron-right"></i></button>';
    host.innerHTML = html;
    host.querySelectorAll('[data-page]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var target = Number(btn.getAttribute('data-page'));
        if (target >= 1 && target <= pages && target !== page) onPage(target);
      });
    });
  }

  window.BXMAdmin = {
    ready: ready,
    fmtDate: fmtDate,
    fmtDateTime: fmtDateTime,
    deny: deny,
    pageSlice: pageSlice,
    renderPager: renderPager
  };
})();
