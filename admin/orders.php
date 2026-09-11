<?php
$adminTitle = 'Manage Orders';
$adminActive = 'orders';
require_once __DIR__ . '/../includes/admin-header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h1 class="bxm-admin-page-title">Orders</h1>
    <p class="bxm-admin-page-sub mb-0">View and track all marketplace orders.</p>
  </div>
</div>

<div class="bxm-form-card mb-3">
  <div class="row g-3">
    <div class="col-md-6"><input type="search" class="form-control" id="orderSearch" placeholder="Search by order ID, email or product..."></div>
    <div class="col-md-3"><select class="form-select" id="orderStatusFilter">
      <option value="">All Statuses</option>
      <option value="payment_submitted">Payment Submitted</option>
      <option value="under_review">Under Review</option>
      <option value="approved">Approved</option>
      <option value="rejected">Rejected</option>
    </select></div>
    <div class="col-md-3"><span class="text-secondary small d-block mt-2" id="orderCount"></span></div>
  </div>
</div>

<div class="bxm-table-wrap">
  <div class="table-responsive">
    <table class="table bxm-table align-middle">
      <thead><tr><th>Order ID</th><th>User</th><th>Product</th><th>Amount</th><th>Transaction ID</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody id="ordersTable"><tr><td colspan="8" class="text-center text-muted py-4">Loading...</td></tr></tbody>
    </table>
  </div>
</div>
<div class="bxm-pager d-flex justify-content-center gap-1 mt-3" id="ordersPager"></div>

<div class="modal fade" id="orderModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content bxm-modal-content">
      <div class="modal-header"><h5 class="modal-title">Order Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body" id="orderModalBody"></div>
      <div class="modal-footer"><button type="button" class="bxm-btn bxm-btn-outline" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>
<?php
$inlineScript = <<<'HTML'
<script>
window.BXMAdmin.ready(function () {
  var db = window.BXM.db;
  var orders = [];
  var page = 1;
  var PER_PAGE = 15;

  function render() {
    var q = (document.getElementById('orderSearch').value || '').toLowerCase();
    var status = document.getElementById('orderStatusFilter').value;
    var list = orders.filter(function (o) {
      if (status && o.status !== status) return false;
      if (q) {
        var hay = (o.orderId + ' ' + (o.userEmail || '') + ' ' + (o.productTitle || '')).toLowerCase();
        if (hay.indexOf(q) === -1) return false;
      }
      return true;
    });
    var slice = window.BXMAdmin.pageSlice(list, page, PER_PAGE);
    page = slice.page;
    document.getElementById('orderCount').textContent = slice.total + ' order(s)';
    var host = document.getElementById('ordersTable');
    if (!slice.items.length) { host.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No orders found.</td></tr>'; }
    else host.innerHTML = slice.items.map(function (o) {
      return '<tr>' +
        '<td>' + window.BXM.escapeHtml(o.orderId || '') + '</td>' +
        '<td class="text-truncate" style="max-width:160px">' + window.BXM.escapeHtml(o.userEmail || o.uid || '') + '</td>' +
        '<td class="text-truncate" style="max-width:180px">' + window.BXM.escapeHtml(o.productTitle || '-') + '</td>' +
        '<td>' + window.BXM.money(o.amount) + '</td>' +
        '<td class="text-truncate" style="max-width:120px">' + window.BXM.escapeHtml(o.transactionId || '-') + '</td>' +
        '<td>' + window.BXMAdmin.fmtDate(o.createdAt) + '</td>' +
        '<td>' + window.BXM.statusBadge(o.status) + '</td>' +
        '<td><button class="bxm-copy-btn" data-view="' + window.BXM.escapeHtml(o.orderId) + '"><i class="bi bi-eye"></i> View</button></td>' +
        '</tr>';
    }).join('');
    window.BXMAdmin.renderPager('ordersPager', slice.page, slice.pages, function (p) { page = p; render(); });
  }

  function escapeDetails(details) {
    if (!details || typeof details !== 'object') return '<p class="text-secondary small mb-0">No delivery details.</p>';
    return Object.keys(details).map(function (k) {
      var f = details[k] || {};
      return '<div class="bxm-delivery-row"><span class="bxm-delivery-key">' + window.BXM.escapeHtml(f.name || '') + '</span><span class="bxm-delivery-val">' + window.BXM.escapeHtml(f.value || '') + '</span></div>';
    }).join('');
  }

  function show(order) {
    var items = '';
    (order.items || []).forEach(function (it) {
      items += '<div class="d-flex justify-content-between small mb-1"><span class="text-secondary">' + window.BXM.escapeHtml(it.title) + ' x' + it.quantity + '</span><span>' + window.BXM.money(it.lineTotal) + '</span></div>';
    });
    var html = '<div class="row g-3 mb-3">' +
      '<div class="col-md-6"><div class="bxm-delivery-row"><span class="bxm-delivery-key">Order ID</span><span class="bxm-delivery-val">' + window.BXM.escapeHtml(order.orderId || '') + '</span></div>' +
      '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Customer</span><span class="bxm-delivery-val">' + window.BXM.escapeHtml(order.userEmail || order.uid || '') + '</span></div>' +
      '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Transaction ID</span><span class="bxm-delivery-val">' + window.BXM.escapeHtml(order.transactionId || '-') + '</span></div></div>' +
      '<div class="col-md-6"><div class="bxm-delivery-row"><span class="bxm-delivery-key">Amount</span><span class="bxm-delivery-val">' + window.BXM.money(order.amount) + '</span></div>' +
      '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Coupon</span><span class="bxm-delivery-val">' + window.BXM.escapeHtml(order.couponCode || 'None') + '</span></div>' +
      '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Status</span><span class="bxm-delivery-val">' + window.BXM.statusBadge(order.status) + '</span></div></div></div>';
    if (items) html += '<div class="bxm-card p-3 mb-3"><h6 class="mb-2">Items</h6>' + items + '</div>';
    if (order.paymentScreenshotUrl) html += '<div class="bxm-card p-3 mb-3"><h6 class="mb-2">Payment Screenshot</h6><img src="' + window.BXM.escapeHtml(order.paymentScreenshotUrl) + '" class="bxm-upload-preview" alt="Payment"></div>';
    if (order.status === 'rejected' && order.rejectionReason) html += '<div class="alert alert-danger border-0" style="background:#2a0f0f;color:#ff8f8f">Rejection reason: ' + window.BXM.escapeHtml(order.rejectionReason) + '</div>';
    if (order.deliveryDetails) html += '<div class="bxm-card p-3"><h6 class="mb-2">Delivery Details</h6>' + escapeDetails(order.deliveryDetails) + '</div>';
    document.getElementById('orderModalBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('orderModal')).show();
  }

  document.getElementById('ordersTable').addEventListener('click', function (e) {
    var btn = e.target.closest('[data-view]');
    if (!btn) return;
    var o = orders.filter(function (x) { return x.orderId === btn.getAttribute('data-view'); })[0];
    if (o) show(o);
  });
  ['orderSearch', 'orderStatusFilter'].forEach(function (id) {
    document.getElementById(id).addEventListener(id === 'orderSearch' ? 'input' : 'change', function () { page = 1; render(); });
  });

  db.ref('orders').once('value').then(function (s) {
    s.forEach(function (c) { var o = c.val() || {}; o.orderId = o.orderId || c.key; orders.push(o); });
    orders.sort(function (a, b) { return (b.createdAt || 0) - (a.createdAt || 0); });
    render();
  });
});
</script>
HTML;
require_once __DIR__ . '/../includes/admin-footer.php';
?>
