<?php
require_once __DIR__ . '/includes/auth-check.php';
$pageTitle = 'Order Details';
$pageDescription = 'View your order status and delivery details.';
$noIndex = true;
require_once __DIR__ . '/includes/header.php';
$orderId = isset($_GET['id']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['id']) : '';
$showCard = isset($_GET['card']);
?>
<div class="container" id="bxmConfigNotice"></div>

<section class="bxm-section-sm">
  <div class="container">
    <nav class="mb-3 small">
      <a href="orders.php" class="bxm-link-muted">My Orders</a>
      <span class="text-muted mx-1">/</span>
      <span class="text-secondary" id="crumbOrder">Order</span>
    </nav>
    <div id="orderDetail">
      <div class="bxm-skeleton" style="height:380px"></div>
    </div>
  </div>
</section>

<div class="modal fade" id="cardModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bxm-modal-content">
      <div class="modal-header"><h5 class="modal-title">Delivery Card</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body" id="cardModalBody"></div>
      <div class="modal-footer"><button type="button" class="bxm-btn bxm-btn-outline" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>
<?php
$encodedOrderId = json_encode($orderId);
$encodedShowCard = $showCard ? 'true' : 'false';
$inlineScript = <<<HTML
<script>
(function () {
  var orderId = {$encodedOrderId};
  var showCard = {$encodedShowCard};
  var currentOrder = null;

  function deliveryCardHTML(order) {
    var rows = '';
    var details = order.deliveryDetails;
    if (details && typeof details === 'object') {
      Object.keys(details).forEach(function (k) {
        var f = details[k] || {};
        rows += '<div class="bxm-delivery-row"><span class="bxm-delivery-key">' + window.BXM.escapeHtml(f.name || '') + '</span>' +
          '<span class="bxm-delivery-val d-flex align-items-center gap-2">' + window.BXM.escapeHtml(f.value || '') +
          '<button class="bxm-copy-btn" data-bxm-copy="' + window.BXM.escapeHtml(f.value || '') + '"><i class="bi bi-clipboard"></i></button></span></div>';
      });
    }
    if (!rows) rows = '<p class="text-secondary mb-0">Delivery details will be added by the admin.</p>';
    return '<div class="bxm-delivery-card">' +
      '<div class="text-center mb-3"><h5 class="mb-0">BLACK X MARKET</h5><div class="small text-muted">Digital Delivery Card</div></div>' +
      '<h6 class="mb-3">' + window.BXM.escapeHtml(order.productTitle || 'Product') + '</h6>' +
      rows +
      '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Order ID</span><span class="bxm-delivery-val">' + window.BXM.escapeHtml(order.orderId || '') + '</span></div>' +
      '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Status</span><span class="bxm-delivery-val text-success">APPROVED</span></div>' +
      '</div>';
  }

  function timeline(status) {
    var steps = [
      { key: 'Order Created', done: true },
      { key: 'Payment Submitted', done: status !== 'pending' },
      { key: 'Under Review', done: ['under_review','approved','rejected','completed'].indexOf(status) !== -1 },
      { key: status === 'rejected' ? 'Rejected' : 'Approved', done: ['approved','rejected','completed'].indexOf(status) !== -1, fail: status === 'rejected' },
      { key: 'Delivery Available', done: ['approved','completed'].indexOf(status) !== -1 }
    ];
    return '<div class="bxm-timeline">' + steps.map(function (s) {
      return '<div class="bxm-timeline-item ' + (s.fail ? 'fail' : (s.done ? 'done' : '')) + '"><div class="' + (s.done ? '' : 'text-muted') + '">' + s.key + '</div></div>';
    }).join('') + '</div>';
  }

  function render(order) {
    currentOrder = order;
    document.getElementById('crumbOrder').textContent = order.orderId || 'Order';
    var status = order.status || 'pending';
    var isApproved = status === 'approved' || status === 'completed';

    var items = '';
    if (order.items && order.items.length) {
      items = order.items.map(function (it) {
        return '<div class="d-flex justify-content-between small mb-1"><span class="text-secondary">' + window.BXM.escapeHtml(it.title) + ' x' + it.quantity + '</span><span>' + window.BXM.money(it.lineTotal) + '</span></div>';
      }).join('');
    } else {
      items = '<div class="d-flex justify-content-between small mb-1"><span class="text-secondary">' + window.BXM.escapeHtml(order.productTitle || '') + '</span><span>' + window.BXM.money(order.amount) + '</span></div>';
    }

    var html = '<div class="row g-4">';
    html += '<div class="col-lg-7">';
    html += '<div class="bxm-card p-4 mb-4">';
    html += '<div class="d-flex justify-content-between align-items-start mb-3"><div><h5 class="mb-1">' + window.BXM.escapeHtml(order.orderId || 'Order') + '</h5><div class="small text-muted">Placed ' + new Date(order.createdAt || Date.now()).toLocaleString() + '</div></div>' + window.BXM.statusBadge(status) + '</div>';
    html += '<hr class="bxm-divider">';
    html += items;
    html += '<div class="d-flex justify-content-between small mb-1"><span class="text-secondary">Coupon (' + window.BXM.escapeHtml(order.couponCode || 'None') + ')</span><span class="text-success">-' + window.BXM.money(order.couponDiscount || 0) + '</span></div>';
    html += '<hr class="bxm-divider">';
    html += '<div class="d-flex justify-content-between"><span class="fw-semibold">Total Paid</span><span class="fw-bold">' + window.BXM.money(order.amount) + '</span></div>';
    html += '</div>';

    html += '<div class="bxm-card p-4 mb-4"><h6 class="mb-3">Order Timeline</h6>' + timeline(status) + '</div>';

    if (status === 'rejected') {
      html += '<div class="bxm-card p-4 mb-4" style="border-color:rgba(239,68,68,.4)"><h6 class="text-danger mb-2">Payment Rejected</h6><p class="text-secondary mb-1"><b>Reason:</b> ' + window.BXM.escapeHtml(order.rejectionReason || 'Transaction could not be verified.') + '</p><a href="' + window.BXM.url('support.php?order=' + encodeURIComponent(order.orderId || '')) + '" class="bxm-btn bxm-btn-outline bxm-btn-sm mt-2">Contact Support</a></div>';
    }

    if (isApproved) {
      html += '<div class="d-flex gap-2 mb-4"><button class="bxm-btn bxm-btn-primary" id="viewCardBtn"><i class="bi bi-card-checklist"></i> View Card</button></div>';
      html += '<div id="inlineCard">' + deliveryCardHTML(order) + '</div>';
    }
    html += '</div>';

    html += '<div class="col-lg-5">';
    html += '<div class="bxm-card p-4 mb-4"><h6 class="mb-3">Payment Details</h6>';
    html += '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Transaction ID</span><span class="bxm-delivery-val">' + window.BXM.escapeHtml(order.transactionId || '-') + '</span></div>';
    html += '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Payment Status</span><span class="bxm-delivery-val">' + window.BXM.statusBadge(status) + '</span></div>';
    html += '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Created</span><span class="bxm-delivery-val">' + new Date(order.createdAt || Date.now()).toLocaleDateString() + '</span></div>';
    html += '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Updated</span><span class="bxm-delivery-val">' + new Date(order.updatedAt || order.createdAt || Date.now()).toLocaleDateString() + '</span></div>';
    html += '</div>';
    if (order.paymentScreenshotUrl) {
      html += '<div class="bxm-card p-4"><h6 class="mb-3">Payment Screenshot</h6><img src="' + window.BXM.escapeHtml(order.paymentScreenshotUrl) + '" class="bxm-upload-preview" alt="Payment screenshot"></div>';
    }
    html += '</div></div>';

    document.getElementById('orderDetail').innerHTML = html;

    var viewBtn = document.getElementById('viewCardBtn');
    if (viewBtn) {
      viewBtn.addEventListener('click', function () {
        document.getElementById('cardModalBody').innerHTML = deliveryCardHTML(currentOrder);
        new bootstrap.Modal(document.getElementById('cardModal')).show();
      });
    }
  }

  function failBox(icon, title) {
    document.getElementById('orderDetail').innerHTML = '<div class="bxm-state-box text-center"><i class="bi ' + icon + ' bxm-state-icon"></i><h5>' + title + '</h5><a href="orders.php" class="bxm-btn bxm-btn-primary mt-2">My Orders</a></div>';
  }

  window.BXM.onAuth(function (user) {
    if (!window.BXM.firebaseReady) {
      failBox('bi-exclamation-triangle', 'Firebase is not configured yet');
      return;
    }
    if (!user) {
      failBox('bi-person-x', 'Please login to view this order');
      return;
    }
    if (!orderId) {
      failBox('bi-receipt', 'Order not found');
      return;
    }
    window.BXM.db.ref('orders/' + orderId).once('value').then(function (snap) {
      var order = snap.val();
      if (!order || (order.uid && order.uid !== user.uid)) {
        failBox('bi-shield-x', 'You are not authorized to view this order');
        return;
      }
      order.orderId = order.orderId || orderId;
      render(order);
      if (showCard && (order.status === 'approved' || order.status === 'completed')) {
        document.getElementById('cardModalBody').innerHTML = deliveryCardHTML(order);
        new bootstrap.Modal(document.getElementById('cardModal')).show();
      }
    }).catch(function () {
      failBox('bi-wifi-off', 'Could not load this order. Please refresh.');
    });
  });
})();
</script>
HTML;
include __DIR__ . '/includes/footer.php';
?>
