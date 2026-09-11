<?php
$adminTitle = 'Dashboard';
$adminActive = 'dashboard';
require_once __DIR__ . '/../includes/admin-header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h1 class="bxm-admin-page-title">Welcome back, Admin</h1>
    <p class="bxm-admin-page-sub mb-0">Here is what is happening in your marketplace.</p>
  </div>
  <a href="<?= bxm_url('admin/payments.php') ?>" class="bxm-btn bxm-btn-primary"><i class="bi bi-credit-card"></i> Review Payments</a>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-lg-3"><div class="bxm-stat-card d-flex justify-content-between align-items-start"><div><div class="bxm-stat-value" id="statUsers">0</div><div class="bxm-stat-label">Total Users</div></div><span class="bxm-stat-icon"><i class="bi bi-people"></i></span></div></div>
  <div class="col-6 col-lg-3"><div class="bxm-stat-card d-flex justify-content-between align-items-start"><div><div class="bxm-stat-value" id="statProducts">0</div><div class="bxm-stat-label">Products</div></div><span class="bxm-stat-icon"><i class="bi bi-box-seam"></i></span></div></div>
  <div class="col-6 col-lg-3"><div class="bxm-stat-card d-flex justify-content-between align-items-start"><div><div class="bxm-stat-value" id="statOrders">0</div><div class="bxm-stat-label">Total Orders</div></div><span class="bxm-stat-icon"><i class="bi bi-receipt"></i></span></div></div>
  <div class="col-6 col-lg-3"><div class="bxm-stat-card d-flex justify-content-between align-items-start"><div><div class="bxm-stat-value" id="statPending">0</div><div class="bxm-stat-label">Pending Payments</div></div><span class="bxm-stat-icon"><i class="bi bi-hourglass-split"></i></span></div></div>
</div>
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3"><div class="bxm-stat-card d-flex justify-content-between align-items-start"><div><div class="bxm-stat-value" id="statRevenue">₹0</div><div class="bxm-stat-label">Revenue</div></div><span class="bxm-stat-icon"><i class="bi bi-cash-stack"></i></span></div></div>
  <div class="col-6 col-lg-3"><div class="bxm-stat-card d-flex justify-content-between align-items-start"><div><div class="bxm-stat-value" id="statApproved">0</div><div class="bxm-stat-label">Approved</div></div><span class="bxm-stat-icon"><i class="bi bi-check-circle"></i></span></div></div>
  <div class="col-6 col-lg-3"><div class="bxm-stat-card d-flex justify-content-between align-items-start"><div><div class="bxm-stat-value" id="statRejected">0</div><div class="bxm-stat-label">Rejected</div></div><span class="bxm-stat-icon"><i class="bi bi-x-circle"></i></span></div></div>
  <div class="col-6 col-lg-3"><div class="bxm-stat-card d-flex justify-content-between align-items-start"><div><div class="bxm-stat-value" id="statCoupons">0</div><div class="bxm-stat-label">Active Coupons</div></div><span class="bxm-stat-icon"><i class="bi bi-ticket-perforated"></i></span></div></div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="bxm-table-wrap">
      <div class="p-3 border-bottom bxm-divider d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Recent Orders</h6>
        <a href="<?= bxm_url('admin/orders.php') ?>" class="small bxm-link-muted">View all</a>
      </div>
      <div class="table-responsive">
        <table class="table bxm-table">
          <thead><tr><th>Order ID</th><th>Customer</th><th>Product</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
          <tbody id="recentOrders"><tr><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="bxm-card p-4 mb-3">
      <h6 class="mb-2">Pending Payments</h6>
      <p class="text-secondary small mb-3"><span id="pendingCount">0</span> payments waiting for verification.</p>
      <a href="<?= bxm_url('admin/payments.php') ?>" class="bxm-btn bxm-btn-outline w-100">Review Payments</a>
    </div>
    <div class="bxm-card p-4">
      <h6 class="mb-3">Recent Users</h6>
      <div id="recentUsers"><p class="text-secondary small mb-0">Loading...</p></div>
    </div>
  </div>
</div>
<?php
$inlineScript = <<<'HTML'
<script>
window.BXMAdmin.ready(function () {
  var db = window.BXM.db;
  var orders = [];
  var users = [];

  function set(id, val) { var el = document.getElementById(id); if (el) el.textContent = val; }

  db.ref('products').once('value').then(function (s) { set('statProducts', s.numChildren()); });
  db.ref('coupons').once('value').then(function (s) {
    var active = 0;
    s.forEach(function (c) { if ((c.val() || {}).status === 'active') active++; });
    set('statCoupons', active);
  });

  db.ref('users').once('value').then(function (s) {
    s.forEach(function (c) {
      var u = c.val() || {};
      u.uid = c.key;
      users.push(u);
    });
    set('statUsers', users.length);
    users.sort(function (a, b) { return (b.createdAt || 0) - (a.createdAt || 0); });
    var host = document.getElementById('recentUsers');
    var recent = users.slice(0, 5);
    if (!recent.length) { host.innerHTML = '<p class="text-secondary small mb-0">No users yet.</p>'; return; }
    host.innerHTML = recent.map(function (u) {
      return '<div class="d-flex justify-content-between align-items-center mb-2"><div class="min-w-0"><div class="small fw-semibold text-truncate">' + window.BXM.escapeHtml(u.name || 'User') + '</div><div class="small text-muted text-truncate">' + window.BXM.escapeHtml(u.email || '') + '</div></div>' + window.BXM.statusBadge(u.status || 'active') + '</div>';
    }).join('');
  }).catch(function () {
    document.getElementById('recentUsers').innerHTML = '<p class="text-secondary small mb-0">User list requires updated database rules.</p>';
  });

  db.ref('orders').once('value').then(function (s) {
    s.forEach(function (c) {
      var o = c.val() || {};
      o.orderId = o.orderId || c.key;
      orders.push(o);
    });
    orders.sort(function (a, b) { return (b.createdAt || 0) - (a.createdAt || 0); });
    var pending = 0, approved = 0, rejected = 0, revenue = 0;
    orders.forEach(function (o) {
      var st = o.status;
      if (st === 'pending' || st === 'payment_submitted' || st === 'under_review') pending++;
      if (st === 'approved' || st === 'completed') { approved++; revenue += window.BXM.toNumber(o.amount); }
      if (st === 'rejected') rejected++;
    });
    set('statOrders', orders.length);
    set('statPending', pending);
    set('statApproved', approved);
    set('statRejected', rejected);
    set('statRevenue', window.BXM.money(revenue));
    document.getElementById('pendingCount').textContent = pending;

    var host = document.getElementById('recentOrders');
    if (!orders.length) { host.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No orders yet.</td></tr>'; return; }
    host.innerHTML = orders.slice(0, 8).map(function (o) {
      return '<tr><td>' + window.BXM.escapeHtml(o.orderId) + '</td><td>' + window.BXM.escapeHtml(o.userEmail || (o.uid || '').slice(0, 8)) + '</td><td class="text-truncate" style="max-width:180px">' + window.BXM.escapeHtml(o.productTitle || '-') + '</td><td>' + window.BXM.money(o.amount) + '</td><td>' + window.BXM.statusBadge(o.status) + '</td><td>' + window.BXMAdmin.fmtDate(o.createdAt) + '</td></tr>';
    }).join('');
  });
});
</script>
HTML;
require_once __DIR__ . '/../includes/admin-footer.php';
?>
