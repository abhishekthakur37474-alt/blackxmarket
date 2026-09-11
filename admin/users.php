<?php
$adminTitle = 'Manage Users';
$adminActive = 'users';
require_once __DIR__ . '/../includes/admin-header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h1 class="bxm-admin-page-title">Users</h1>
    <p class="bxm-admin-page-sub mb-0">Manage registered customers and their access.</p>
  </div>
</div>

<div class="bxm-form-card mb-3">
  <div class="row g-3">
    <div class="col-md-6"><input type="search" class="form-control" id="userSearch" placeholder="Search by name or email..."></div>
    <div class="col-md-3"><select class="form-select" id="userStatusFilter"><option value="">All Statuses</option><option value="active">Active</option><option value="banned">Disabled</option></select></div>
    <div class="col-md-3"><span class="text-secondary small d-block mt-2" id="userCount"></span></div>
  </div>
</div>

<div class="bxm-table-wrap">
  <div class="table-responsive">
    <table class="table bxm-table align-middle">
      <thead><tr><th>User</th><th>Email</th><th>Registered</th><th>Orders</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody id="usersTable"><tr><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr></tbody>
    </table>
  </div>
</div>
<div class="bxm-pager d-flex justify-content-center gap-1 mt-3" id="usersPager"></div>

<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bxm-modal-content">
      <div class="modal-header"><h5 class="modal-title">User Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body" id="userModalBody"></div>
      <div class="modal-footer"><button type="button" class="bxm-btn bxm-btn-outline" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>
<?php
$inlineScript = <<<'HTML'
<script>
window.BXMAdmin.ready(function () {
  var db = window.BXM.db;
  var users = [];
  var orderCounts = {};
  var userOrders = {};
  var page = 1;
  var PER_PAGE = 15;

  function render() {
    var q = (document.getElementById('userSearch').value || '').toLowerCase();
    var status = document.getElementById('userStatusFilter').value;
    var list = users.filter(function (u) {
      if (status && (u.status || 'active') !== status) return false;
      if (q && (u.name || '').toLowerCase().indexOf(q) === -1 && (u.email || '').toLowerCase().indexOf(q) === -1) return false;
      return true;
    });
    var slice = window.BXMAdmin.pageSlice(list, page, PER_PAGE);
    page = slice.page;
    document.getElementById('userCount').textContent = slice.total + ' user(s)';
    var host = document.getElementById('usersTable');
    if (!slice.items.length) { host.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>'; }
    else host.innerHTML = slice.items.map(function (u) {
      var banned = u.status === 'banned';
      return '<tr>' +
        '<td><div class="d-flex align-items-center gap-2"><img src="' + window.BXM.escapeHtml(u.photoUrl || window.BXM.url('assets/images/logo/logo.jpeg')) + '" class="bxm-table-thumb" style="width:34px;height:34px;border-radius:50%" alt=""><span>' + window.BXM.escapeHtml(u.name || 'User') + '</span></div></td>' +
        '<td class="text-truncate" style="max-width:180px">' + window.BXM.escapeHtml(u.email || '') + '</td>' +
        '<td>' + window.BXMAdmin.fmtDate(u.createdAt) + '</td>' +
        '<td>' + (orderCounts[u.uid] || 0) + '</td>' +
        '<td>' + window.BXM.statusBadge(banned ? 'inactive' : 'active') + '</td>' +
        '<td><div class="bxm-table-actions">' +
        '<button class="bxm-copy-btn" data-view="' + u.uid + '" title="View"><i class="bi bi-eye"></i></button>' +
        '<button class="bxm-copy-btn" data-toggle-user="' + u.uid + '" title="' + (banned ? 'Enable' : 'Disable') + '"><i class="bi ' + (banned ? 'bi-unlock' : 'bi-lock') + '"></i></button>' +
        '</div></td></tr>';
    }).join('');
    window.BXMAdmin.renderPager('usersPager', slice.page, slice.pages, function (p) { page = p; render(); });
  }

  function showUser(u) {
    var recent = (userOrders[u.uid] || []).slice(0, 5);
    var html = '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Name</span><span class="bxm-delivery-val">' + window.BXM.escapeHtml(u.name || '') + '</span></div>' +
      '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Email</span><span class="bxm-delivery-val">' + window.BXM.escapeHtml(u.email || '') + '</span></div>' +
      '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Role</span><span class="bxm-delivery-val">' + window.BXM.escapeHtml(u.role || 'user') + '</span></div>' +
      '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Registered</span><span class="bxm-delivery-val">' + window.BXMAdmin.fmtDateTime(u.createdAt) + '</span></div>' +
      '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Total Orders</span><span class="bxm-delivery-val">' + (orderCounts[u.uid] || 0) + '</span></div>' +
      '<h6 class="mt-3 mb-2">Recent Orders</h6>' +
      (recent.length ? recent.map(function (o) {
        return '<div class="d-flex justify-content-between small mb-1"><span class="text-secondary">' + window.BXM.escapeHtml(o.orderId) + '</span><span>' + window.BXM.money(o.amount) + ' ' + window.BXM.statusBadge(o.status) + '</span></div>';
      }).join('') : '<p class="text-secondary small">No orders yet.</p>');
    document.getElementById('userModalBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('userModal')).show();
  }

  document.getElementById('usersTable').addEventListener('click', function (e) {
    var view = e.target.closest('[data-view]');
    var toggle = e.target.closest('[data-toggle-user]');
    if (view) {
      var u = users.filter(function (x) { return x.uid === view.getAttribute('data-view'); })[0];
      if (u) showUser(u);
    }
    if (toggle) {
      var uid = toggle.getAttribute('data-toggle-user');
      var target = users.filter(function (x) { return x.uid === uid; })[0];
      var next = (target && target.status === 'banned') ? 'active' : 'banned';
      db.ref('users/' + uid).update({ status: next }).then(function () {
        window.BXM.toast('User ' + (next === 'banned' ? 'disabled' : 'enabled'), 'success');
        load();
      });
    }
  });

  ['userSearch', 'userStatusFilter'].forEach(function (id) {
    document.getElementById(id).addEventListener(id === 'userSearch' ? 'input' : 'change', function () { page = 1; render(); });
  });

  function load() {
    db.ref('orders').once('value').then(function (s) {
      orderCounts = {}; userOrders = {};
      s.forEach(function (c) {
        var o = c.val() || {};
        o.orderId = o.orderId || c.key;
        orderCounts[o.uid] = (orderCounts[o.uid] || 0) + 1;
        (userOrders[o.uid] = userOrders[o.uid] || []).push(o);
      });
    });
    db.ref('users').once('value').then(function (s) {
      users = [];
      s.forEach(function (c) { var u = c.val() || {}; u.uid = c.key; users.push(u); });
      users.sort(function (a, b) { return (b.createdAt || 0) - (a.createdAt || 0); });
      render();
    }).catch(function () {
      document.getElementById('usersTable').innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">User list requires root-level admin read rules. Update your Realtime Database rules.</td></tr>';
    });
  }

  load();
});
</script>
HTML;
require_once __DIR__ . '/../includes/admin-footer.php';
?>
