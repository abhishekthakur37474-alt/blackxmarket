<?php
$adminTitle = 'Manage Coupons';
$adminActive = 'coupons';
require_once __DIR__ . '/../includes/admin-header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h1 class="bxm-admin-page-title">Coupons</h1>
    <p class="bxm-admin-page-sub mb-0">Create and manage discount coupons.</p>
  </div>
  <button class="bxm-btn bxm-btn-primary" id="addCouponBtn"><i class="bi bi-plus-lg"></i> Create Coupon</button>
</div>

<div class="bxm-table-wrap">
  <div class="table-responsive">
    <table class="table bxm-table align-middle">
      <thead><tr><th>Code</th><th>Discount</th><th>Min Amount</th><th>Max Discount</th><th>Usage</th><th>Expiry</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody id="couponsTable"><tr><td colspan="8" class="text-center text-muted py-4">Loading...</td></tr></tbody>
    </table>
  </div>
</div>
<div class="bxm-pager d-flex justify-content-center gap-1 mt-3" id="couponsPager"></div>

<div class="modal fade" id="couponModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content bxm-modal-content">
      <div class="modal-header"><h5 class="modal-title" id="couponModalTitle">Create Coupon</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" id="cpId">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Coupon Code</label><input type="text" class="form-control" id="cpCode" placeholder="GAMING20" style="text-transform:uppercase"></div>
          <div class="col-md-6"><label class="form-label">Type</label><select class="form-select" id="cpType"><option value="percentage">Percentage %</option><option value="flat">Flat Amount</option></select></div>
          <div class="col-md-4"><label class="form-label">Discount Value</label><input type="number" min="0" step="0.01" class="form-control" id="cpValue" placeholder="20"></div>
          <div class="col-md-4"><label class="form-label">Minimum Amount</label><input type="number" min="0" step="0.01" class="form-control" id="cpMin" placeholder="500"></div>
          <div class="col-md-4"><label class="form-label">Maximum Discount</label><input type="number" min="0" step="0.01" class="form-control" id="cpMax" placeholder="200"></div>
          <div class="col-md-4"><label class="form-label">Expiry Date</label><input type="date" class="form-control" id="cpExpiry"></div>
          <div class="col-md-4"><label class="form-label">Usage Limit</label><input type="number" min="0" class="form-control" id="cpLimit" placeholder="100"></div>
          <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" id="cpStatus"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="bxm-btn bxm-btn-outline" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="bxm-btn bxm-btn-primary" id="saveCouponBtn">Save Coupon</button>
      </div>
    </div>
  </div>
</div>
<?php
$inlineScript = <<<'HTML'
<script>
window.BXMAdmin.ready(function () {
  var db = window.BXM.db;
  var coupons = [];
  var editingId = null;
  var page = 1;
  var PER_PAGE = 15;

  function render() {
    var host = document.getElementById('couponsTable');
    var slice = window.BXMAdmin.pageSlice(coupons, page, PER_PAGE);
    page = slice.page;
    if (!slice.items.length) { host.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No coupons yet.</td></tr>'; window.BXMAdmin.renderPager('couponsPager', slice.page, slice.pages, function (p) { page = p; render(); }); return; }
    host.innerHTML = slice.items.map(function (c) {
      var discount = c.type === 'flat' ? window.BXM.money(c.value) : window.BXM.toNumber(c.value) + '%';
      return '<tr>' +
        '<td class="fw-semibold">' + window.BXM.escapeHtml(c.code || '') + '</td>' +
        '<td>' + discount + '</td>' +
        '<td>' + window.BXM.money(c.minAmount || 0) + '</td>' +
        '<td>' + (window.BXM.toNumber(c.maxDiscount) > 0 ? window.BXM.money(c.maxDiscount) : '-') + '</td>' +
        '<td>' + window.BXM.toNumber(c.usedCount) + (window.BXM.toNumber(c.usageLimit) > 0 ? ' / ' + window.BXM.toNumber(c.usageLimit) : '') + '</td>' +
        '<td>' + window.BXM.escapeHtml(c.expiryDate || '-') + '</td>' +
        '<td>' + window.BXM.statusBadge(c.status || 'active') + '</td>' +
        '<td><div class="bxm-table-actions">' +
        '<button class="bxm-copy-btn" data-edit="' + c.id + '"><i class="bi bi-pencil"></i></button>' +
        '<button class="bxm-copy-btn" data-toggle="' + c.id + '"><i class="bi ' + (c.status === 'active' ? 'bi-eye-slash' : 'bi-eye') + '"></i></button>' +
        '<button class="bxm-copy-btn text-danger" data-del="' + c.id + '"><i class="bi bi-trash"></i></button>' +
        '</div></td></tr>';
    }).join('');
    window.BXMAdmin.renderPager('couponsPager', slice.page, slice.pages, function (p) { page = p; render(); });
  }

  function load() {
    db.ref('coupons').once('value').then(function (s) {
      coupons = [];
      s.forEach(function (c) { var v = c.val() || {}; v.id = c.key; coupons.push(v); });
      render();
    });
  }

  function openModal(coupon) {
    editingId = coupon ? coupon.id : null;
    document.getElementById('couponModalTitle').textContent = coupon ? 'Edit Coupon' : 'Create Coupon';
    document.getElementById('cpCode').value = coupon ? (coupon.code || '') : '';
    document.getElementById('cpType').value = coupon ? (coupon.type || 'percentage') : 'percentage';
    document.getElementById('cpValue').value = coupon ? (coupon.value || '') : '';
    document.getElementById('cpMin').value = coupon ? (coupon.minAmount || '') : '';
    document.getElementById('cpMax').value = coupon ? (coupon.maxDiscount || '') : '';
    document.getElementById('cpExpiry').value = coupon ? (coupon.expiryDate || '') : '';
    document.getElementById('cpLimit').value = coupon ? (coupon.usageLimit || '') : '';
    document.getElementById('cpStatus').value = coupon ? (coupon.status || 'active') : 'active';
    new bootstrap.Modal(document.getElementById('couponModal')).show();
  }

  document.getElementById('addCouponBtn').addEventListener('click', function () { openModal(null); });

  document.getElementById('couponsTable').addEventListener('click', function (e) {
    var btn = e.target.closest('button');
    if (!btn) return;
    if (btn.hasAttribute('data-edit')) {
      var c = coupons.filter(function (x) { return x.id === btn.getAttribute('data-edit'); })[0];
      if (c) openModal(c);
    } else if (btn.hasAttribute('data-toggle')) {
      var item = coupons.filter(function (x) { return x.id === btn.getAttribute('data-toggle'); })[0];
      db.ref('coupons/' + item.id).update({ status: item.status === 'active' ? 'inactive' : 'active' }).then(function () { window.BXM.toast('Status updated', 'success'); load(); });
    } else if (btn.hasAttribute('data-del')) {
      var id = btn.getAttribute('data-del');
      window.BXM.confirmDialog('Delete this coupon?', { confirmText: 'Delete', danger: true }).then(function (ok) {
        if (!ok) return;
        db.ref('coupons/' + id).remove().then(function () { window.BXM.toast('Coupon deleted', 'info'); load(); });
      });
    }
  });

  document.getElementById('saveCouponBtn').addEventListener('click', function () {
    var code = document.getElementById('cpCode').value.trim().toUpperCase();
    var value = document.getElementById('cpValue').value;
    if (!code) { window.BXM.toast('Coupon code is required', 'warning'); return; }
    if (value === '') { window.BXM.toast('Discount value is required', 'warning'); return; }
    var data = {
      code: code,
      type: document.getElementById('cpType').value,
      value: window.BXM.toNumber(value),
      minAmount: window.BXM.toNumber(document.getElementById('cpMin').value),
      maxDiscount: window.BXM.toNumber(document.getElementById('cpMax').value),
      expiryDate: document.getElementById('cpExpiry').value,
      usageLimit: window.BXM.toNumber(document.getElementById('cpLimit').value),
      status: document.getElementById('cpStatus').value,
      updatedAt: Date.now()
    };
    if (!editingId) { data.usedCount = 0; data.createdAt = Date.now(); }
    var op = editingId ? db.ref('coupons/' + editingId).update(data) : db.ref('coupons').push(data);
    op.then(function () {
      bootstrap.Modal.getInstance(document.getElementById('couponModal')).hide();
      window.BXM.toast(editingId ? 'Coupon updated' : 'Coupon created', 'success');
      load();
    }).catch(function () { window.BXM.toast('Could not save coupon', 'danger'); });
  });

  load();
});
</script>
HTML;
require_once __DIR__ . '/../includes/admin-footer.php';
?>
