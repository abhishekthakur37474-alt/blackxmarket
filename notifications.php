<?php
require_once __DIR__ . '/includes/auth-check.php';
$pageTitle = 'Notifications';
$pageDescription = 'Your account notifications and order updates.';
$noIndex = true;
require_once __DIR__ . '/includes/header.php';
?>
<div class="container" id="bxmConfigNotice"></div>

<section class="bxm-section-sm">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <div>
        <h1 class="bxm-section-title mb-1">Notifications</h1>
        <p class="bxm-section-sub mb-0">Order and account updates for you.</p>
      </div>
      <button type="button" class="bxm-btn bxm-btn-outline bxm-btn-sm" id="notifMarkAllBtn"><i class="bi bi-check2-all"></i> Mark all as read</button>
    </div>
    <div class="bxm-card p-0" id="notifFullList">
      <div class="p-4"><div class="bxm-skeleton" style="height:64px"></div></div>
    </div>
  </div>
</section>
<?php
$inlineScript = <<<'HTML'
<script>
(function () {
  var notifications = [];

  function icon(type) {
    var map = {
      success: 'bi-check-circle-fill text-success',
      danger: 'bi-x-circle-fill text-danger',
      warning: 'bi-exclamation-triangle-fill text-warning',
      info: 'bi-info-circle-fill text-info'
    };
    return map[type] || map.info;
  }

  function render() {
    var host = document.getElementById('notifFullList');
    if (!notifications.length) {
      host.innerHTML = '<div class="bxm-state-box text-center"><i class="bi bi-bell-slash bxm-state-icon"></i><h5>No notifications yet</h5><p class="text-secondary mb-0">Order and account updates will appear here.</p></div>';
      return;
    }
    host.innerHTML = notifications.map(function (n) {
      var unread = !n.read;
      return '<a class="bxm-notif-item bxm-notif-item-lg' + (unread ? ' unread' : '') + '" href="' + window.BXM.escapeHtml(n.link || '#') + '" data-id="' + window.BXM.escapeHtml(n.id) + '">' +
        '<span class="bxm-notif-icon"><i class="bi ' + icon(n.type) + '"></i></span>' +
        '<span class="flex-grow-1 min-w-0"><span class="bxm-notif-title">' + window.BXM.escapeHtml(n.title || 'Notification') + '</span>' +
        '<span class="bxm-notif-msg">' + window.BXM.escapeHtml(n.message || '') + '</span>' +
        '<span class="bxm-notif-time">' + window.BXM.timeAgo(n.createdAt) + '</span></span>' +
        (unread ? '<span class="bxm-notif-dot"></span>' : '') +
        '</a>';
    }).join('');
  }

  function markRead(id) {
    if (!window.BXM.user) return;
    window.BXM.db.ref('notifications/' + window.BXM.user.uid + '/' + id + '/read').set(true);
  }

  document.getElementById('notifFullList').addEventListener('click', function (e) {
    var item = e.target.closest('[data-id]');
    if (item) markRead(item.getAttribute('data-id'));
  });

  document.getElementById('notifMarkAllBtn').addEventListener('click', function () {
    window.BXM.markAllNotificationsRead();
  });

  window.BXM.onAuth(function (user) {
    if (!window.BXM.firebaseReady || !user) return;
    window.BXM.db.ref('notifications/' + user.uid).orderByChild('createdAt').on('value', function (snap) {
      notifications = [];
      snap.forEach(function (c) {
        var n = c.val() || {};
        n.id = c.key;
        notifications.push(n);
      });
      notifications.sort(function (a, b) { return Number(b.createdAt || 0) - Number(a.createdAt || 0); });
      render();
    });
  });
})();
</script>
HTML;
include __DIR__ . '/includes/footer.php';
?>
