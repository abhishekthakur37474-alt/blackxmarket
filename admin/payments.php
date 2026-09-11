<?php
$adminTitle = 'Manage Payments';
$adminActive = 'payments';
require_once __DIR__ . '/../includes/admin-header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h1 class="bxm-admin-page-title">Payment Verification</h1>
    <p class="bxm-admin-page-sub mb-0">Review submitted payments and approve or reject orders.</p>
  </div>
  <div class="bxm-tabs d-flex gap-2">
    <button class="bxm-btn bxm-btn-primary bxm-btn-sm" data-pfilter="pending">Pending</button>
    <button class="bxm-btn bxm-btn-outline bxm-btn-sm" data-pfilter="approved">Approved</button>
    <button class="bxm-btn bxm-btn-outline bxm-btn-sm" data-pfilter="rejected">Rejected</button>
    <button class="bxm-btn bxm-btn-outline bxm-btn-sm" data-pfilter="all">All</button>
  </div>
</div>

<div class="row g-3" id="paymentsList">
  <div class="col-12"><div class="bxm-skeleton" style="height:200px"></div></div>
</div>
<div class="bxm-pager d-flex justify-content-center gap-1 mt-3" id="paymentsPager"></div>

<div class="modal fade" id="approveModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content bxm-modal-content">
      <div class="modal-header"><h5 class="modal-title">Add Delivery Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p class="text-secondary small">Add the digital delivery details for this order. The customer can access them from their delivery card after approval.</p>
        <div id="deliveryList"></div>
        <button type="button" class="bxm-btn bxm-btn-outline bxm-btn-sm" id="addDeliveryBtn"><i class="bi bi-plus"></i> Add Another Detail</button>
      </div>
      <div class="modal-footer">
        <button type="button" class="bxm-btn bxm-btn-outline" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="bxm-btn bxm-btn-success" id="approveDeliverBtn"><i class="bi bi-check2-circle"></i> Approve &amp; Deliver</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bxm-modal-content">
      <div class="modal-header"><h5 class="modal-title">Reject Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <label class="form-label">Reason for rejection</label>
        <textarea class="form-control" id="rejectionReason" rows="4" placeholder="Transaction could not be verified..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="bxm-btn bxm-btn-outline" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="bxm-btn bxm-btn-danger" id="rejectConfirmBtn">Reject Payment</button>
      </div>
    </div>
  </div>
</div>
<?php
$inlineScript = <<<'HTML'
<script>
window.BXMAdmin.ready(function () {
  var db = window.BXM.db;
  var orders = [];
  var filter = 'pending';
  var activeOrder = null;
  var page = 1;
  var PER_PAGE = 10;

  function isPending(o) { return o.status === 'pending' || o.status === 'payment_submitted' || o.status === 'under_review'; }

  function render() {
    var list = orders.filter(function (o) {
      if (filter === 'pending') return isPending(o);
      if (filter === 'all') return true;
      return o.status === filter;
    });
    var slice = window.BXMAdmin.pageSlice(list, page, PER_PAGE);
    page = slice.page;
    var host = document.getElementById('paymentsList');
    if (!slice.items.length) {
      host.innerHTML = '<div class="col-12"><div class="bxm-state-box text-center"><i class="bi bi-credit-card bxm-state-icon"></i><p class="text-secondary mb-0">No payments in this category.</p></div></div>';
      window.BXMAdmin.renderPager('paymentsPager', slice.page, slice.pages, function (p) { page = p; render(); });
      return;
    }
    host.innerHTML = slice.items.map(function (o) {
      var actions = '';
      if (isPending(o)) {
        actions = '<div class="d-flex gap-2 mt-3"><button class="bxm-btn bxm-btn-danger bxm-btn-sm" data-reject="' + window.BXM.escapeHtml(o.orderId) + '"><i class="bi bi-x-lg"></i> Reject</button>' +
          '<button class="bxm-btn bxm-btn-success bxm-btn-sm" data-approve="' + window.BXM.escapeHtml(o.orderId) + '"><i class="bi bi-check-lg"></i> Approve &amp; Add Delivery</button></div>';
      } else {
        actions = '<div class="mt-3">' + (o.status === 'rejected' && o.rejectionReason ? '<span class="small text-danger">Reason: ' + window.BXM.escapeHtml(o.rejectionReason) + '</span>' : '<span class="small text-success">Delivery available to customer</span>') + '</div>';
      }
      return '<div class="col-lg-6"><div class="bxm-card p-4">' +
        '<div class="d-flex justify-content-between align-items-start mb-2"><div><div class="fw-semibold">' + window.BXM.escapeHtml(o.orderId) + '</div><div class="small text-muted">' + window.BXMAdmin.fmtDateTime(o.createdAt) + '</div></div>' + window.BXM.statusBadge(o.status) + '</div>' +
        '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Customer</span><span class="bxm-delivery-val">' + window.BXM.escapeHtml(o.userEmail || o.uid || '') + '</span></div>' +
        '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Product</span><span class="bxm-delivery-val">' + window.BXM.escapeHtml(o.productTitle || '-') + '</span></div>' +
        '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Amount</span><span class="bxm-delivery-val">' + window.BXM.money(o.amount) + '</span></div>' +
        '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Transaction ID</span><span class="bxm-delivery-val">' + window.BXM.escapeHtml(o.transactionId || '-') + '</span></div>' +
        (o.paymentScreenshotUrl ? '<div class="mt-3"><a href="' + window.BXM.escapeHtml(o.paymentScreenshotUrl) + '" target="_blank" rel="noopener"><img src="' + window.BXM.escapeHtml(o.paymentScreenshotUrl) + '" class="bxm-upload-preview" style="max-height:200px" alt="Payment screenshot"></a></div>' : '') +
        actions + '</div></div>';
    }).join('');
    window.BXMAdmin.renderPager('paymentsPager', slice.page, slice.pages, function (p) { page = p; render(); });
  }

  function deliveryRow(name, value) {
    var div = document.createElement('div');
    div.className = 'bxm-detail-field';
    div.innerHTML = '<input type="text" class="form-control form-control-sm" placeholder="Name" value="' + window.BXM.escapeHtml(name || '') + '" data-dname>' +
      '<input type="text" class="form-control form-control-sm" placeholder="Value" value="' + window.BXM.escapeHtml(value || '') + '" data-dvalue>' +
      '<button type="button" class="bxm-copy-btn" data-remove-delivery><i class="bi bi-trash"></i></button>';
    document.getElementById('deliveryList').appendChild(div);
  }

  document.getElementById('paymentsList').addEventListener('click', function (e) {
    var app = e.target.closest('[data-approve]');
    var rej = e.target.closest('[data-reject]');
    if (app) {
      activeOrder = orders.filter(function (o) { return o.orderId === app.getAttribute('data-approve'); })[0];
      document.getElementById('deliveryList').innerHTML = '';
      if (activeOrder && activeOrder.deliveryDetails) {
        Object.keys(activeOrder.deliveryDetails).forEach(function (k) {
          var f = activeOrder.deliveryDetails[k] || {};
          deliveryRow(f.name, f.value);
        });
      }
      deliveryRow('Name', 'BLACK X MARKET');
      new bootstrap.Modal(document.getElementById('approveModal')).show();
    }
    if (rej) {
      activeOrder = orders.filter(function (o) { return o.orderId === rej.getAttribute('data-reject'); })[0];
      document.getElementById('rejectionReason').value = '';
      new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }
  });

  document.getElementById('addDeliveryBtn').addEventListener('click', function () { deliveryRow('', ''); });
  document.getElementById('deliveryList').addEventListener('click', function (e) {
    if (e.target.closest('[data-remove-delivery]')) e.target.closest('.bxm-detail-field').remove();
  });

  document.getElementById('approveDeliverBtn').addEventListener('click', function () {
    if (!activeOrder) return;
    var details = {};
    var order = 0;
    var empty = true;
    document.querySelectorAll('#deliveryList .bxm-detail-field').forEach(function (row) {
      var name = row.querySelector('[data-dname]').value.trim();
      var value = row.querySelector('[data-dvalue]').value.trim();
      if (name) { details['d' + (order + 1)] = { name: name, value: value, order: order }; order++; empty = false; }
    });
    if (empty) { window.BXM.toast('Add at least one delivery detail', 'warning'); return; }
    var btn = document.getElementById('approveDeliverBtn');
    btn.disabled = true;
    db.ref('orders/' + activeOrder.orderId).update({ status: 'approved', deliveryDetails: details, rejectionReason: '', updatedAt: Date.now() })
      .then(function () {
        btn.disabled = false;
        if (activeOrder.uid) {
          window.BXM.pushNotification(activeOrder.uid, {
            title: 'Order approved',
            message: 'Your order ' + activeOrder.orderId + ' is approved. Your delivery card is now available.',
            type: 'success',
            link: window.BXM.url('order-details.php?id=' + encodeURIComponent(activeOrder.orderId) + '&card=1')
          });
        }
        bootstrap.Modal.getInstance(document.getElementById('approveModal')).hide();
        window.BXM.toast('Order approved and delivered', 'success');
        reload();
      }).catch(function () { btn.disabled = false; window.BXM.toast('Could not approve order', 'danger'); });
  });

  document.getElementById('rejectConfirmBtn').addEventListener('click', function () {
    if (!activeOrder) return;
    var reason = document.getElementById('rejectionReason').value.trim();
    if (!reason) { window.BXM.toast('Please enter a rejection reason', 'warning'); return; }
    var btn = document.getElementById('rejectConfirmBtn');
    btn.disabled = true;
    db.ref('orders/' + activeOrder.orderId).update({ status: 'rejected', rejectionReason: reason, updatedAt: Date.now() })
      .then(function () {
        btn.disabled = false;
        if (activeOrder.uid) {
          window.BXM.pushNotification(activeOrder.uid, {
            title: 'Payment rejected',
            message: 'Your payment for order ' + activeOrder.orderId + ' was rejected. Reason: ' + reason,
            type: 'danger',
            link: window.BXM.url('order-details.php?id=' + encodeURIComponent(activeOrder.orderId))
          });
        }
        bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
        window.BXM.toast('Payment rejected', 'info');
        reload();
      }).catch(function () { btn.disabled = false; window.BXM.toast('Could not reject order', 'danger'); });
  });

  document.querySelectorAll('[data-pfilter]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      filter = btn.getAttribute('data-pfilter');
      page = 1;
      document.querySelectorAll('[data-pfilter]').forEach(function (b) { b.classList.remove('active', 'bxm-btn-primary'); b.classList.add('bxm-btn-outline'); });
      btn.classList.add('active', 'bxm-btn-primary');
      render();
    });
  });

  function reload() {
    db.ref('orders').once('value').then(function (s) {
      orders = [];
      s.forEach(function (c) { var o = c.val() || {}; o.orderId = o.orderId || c.key; orders.push(o); });
      orders.sort(function (a, b) { return (b.createdAt || 0) - (a.createdAt || 0); });
      render();
    });
  }

  reload();
});
</script>
HTML;
require_once __DIR__ . '/../includes/admin-footer.php';
?>
