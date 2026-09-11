<?php
require_once __DIR__ . '/includes/auth-check.php';
$pageTitle = 'Payment';
$pageDescription = 'Complete payment and submit verification details.';
$noIndex = true;
require_once __DIR__ . '/includes/header.php';
?>
<div class="container" id="bxmConfigNotice"></div>

<section class="bxm-section-sm">
  <div class="container">
    <nav class="mb-3 small">
      <a href="cart.php" class="bxm-link-muted">Cart</a>
      <span class="text-muted mx-1">/</span>
      <a href="checkout.php" class="bxm-link-muted">Checkout</a>
      <span class="text-muted mx-1">/</span>
      <span class="text-secondary">Payment</span>
    </nav>
    <h1 class="bxm-section-title mb-4">Payment</h1>
    <div class="row g-4">
      <div class="col-lg-7">
        <div class="bxm-card p-4 mb-4">
          <h6 class="mb-3">Payment Instructions</h6>
          <div id="paymentInfo"><div class="bxm-skeleton" style="height:120px"></div></div>
        </div>

        <div class="bxm-card p-4">
          <h6 class="mb-3">Submit Payment</h6>
          <form id="paymentForm" novalidate>
            <div class="mb-3">
              <label class="form-label">Payment Screenshot</label>
              <input type="file" class="form-control" id="screenshotInput" accept="image/jpeg,image/png,image/webp" required>
              <div class="form-text">JPG, PNG or WEBP. Max 5MB.</div>
            </div>
            <img id="screenshotPreview" class="bxm-upload-preview mb-3" hidden alt="Payment screenshot preview">
            <div class="mb-4">
              <label class="form-label">Transaction ID</label>
              <input type="text" class="form-control" id="transactionId" placeholder="Enter transaction / UTR ID" required>
            </div>
            <button type="submit" class="bxm-btn bxm-btn-primary w-100" id="submitPaymentBtn"><i class="bi bi-send"></i> Submit Payment</button>
          </form>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="bxm-card p-4 bxm-sticky-summary">
          <h6 class="mb-3">Order Summary</h6>
          <div id="summaryItems" class="mb-3"><div class="bxm-skeleton" style="height:60px"></div></div>
          <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Subtotal</span><span id="summarySubtotal">₹0.00</span></div>
          <div class="d-flex justify-content-between mb-3"><span class="text-secondary">Coupon Discount</span><span class="text-success" id="summaryDiscount">-₹0.00</span></div>
          <hr class="bxm-divider my-3">
          <div class="d-flex justify-content-between mb-2"><span class="fw-semibold">Amount Payable</span><span class="fw-bold" id="summaryTotal">₹0.00</span></div>
          <p class="text-muted small mb-0">Pay the exact amount, then upload the screenshot and transaction ID.</p>
          <a href="checkout.php" class="bxm-btn bxm-btn-ghost w-100 mt-3">Back to Checkout</a>
        </div>
      </div>
    </div>
  </div>
</section>
<?php
$inlineScript = <<<'HTML'
<script>
(function () {
  var products = {};
  var cartReady = false;
  var productsReady = false;
  var appliedCoupon = '';
  var appliedDiscount = 0;
  var subtotal = 0;

  try {
    var saved = JSON.parse(sessionStorage.getItem('bxmCheckout') || '{}');
    appliedCoupon = saved.coupon || '';
    appliedDiscount = Number(saved.discount) || 0;
  } catch (e) {}

  function cartIds() {
    return Object.keys(window.BXM.state.cart || {}).filter(function (id) { return products[id]; });
  }

  function computeSubtotal() {
    var cart = window.BXM.state.cart || {};
    var total = 0;
    var rows = '';
    cartIds().forEach(function (id) {
      var p = products[id];
      var qty = Number(cart[id].quantity) || 1;
      var price = window.BXM.toNumber(p.discountedPrice || p.originalPrice);
      total += price * qty;
      rows += '<div class="d-flex justify-content-between align-items-center mb-2 small">' +
        '<span class="text-secondary text-truncate me-2">' + window.BXM.escapeHtml(p.title || 'Product') + ' x' + qty + '</span>' +
        '<span>' + window.BXM.money(price * qty) + '</span></div>';
    });
    document.getElementById('summaryItems').innerHTML = rows || '<p class="text-secondary small mb-0">Loading cart...</p>';
    return total;
  }

  function render() {
    if (!productsReady || !cartReady) return;
    if (!cartIds().length) {
      document.getElementById('summaryItems').innerHTML = '<p class="text-secondary mb-2">Your cart is empty.</p><a href="' + window.BXM.url('cart.php') + '" class="bxm-btn bxm-btn-outline bxm-btn-sm">Go to Cart</a>';
      return;
    }
    subtotal = computeSubtotal();
    document.getElementById('summarySubtotal').textContent = window.BXM.money(subtotal);
    document.getElementById('summaryDiscount').textContent = '-' + window.BXM.money(appliedDiscount);
    document.getElementById('summaryTotal').textContent = window.BXM.money(Math.max(0, subtotal - appliedDiscount));
    updatePayable();
  }

  function loadPaymentInfo() {
    window.BXM.db.ref('settings/payment').once('value').then(function (snap) {
      var s = snap.val() || {};
      var host = document.getElementById('paymentInfo');
      if (s.active === false || (!s.qrCodeUrl && !s.upiId)) {
        host.innerHTML = '<p class="text-secondary mb-0">Payment information is currently unavailable. Please contact support.</p>';
        return;
      }
      var html = '';
      html += '<div class="mb-2"><span class="text-secondary">Amount Payable:</span> <span class="fw-bold" id="payableAmount">₹0.00</span></div>';
      if (s.qrCodeUrl) {
        html += '<div class="text-center mb-3"><div class="bxm-qr-box"><img src="' + window.BXM.escapeHtml(s.qrCodeUrl) + '" alt="Payment QR"></div></div>';
      }
      if (s.upiId) {
        html += '<div class="mb-3"><label class="form-label">UPI ID</label><div class="input-group">' +
          '<input type="text" class="form-control" value="' + window.BXM.escapeHtml(s.upiId) + '" readonly>' +
          '<button class="bxm-btn bxm-btn-outline" type="button" data-bxm-copy="' + window.BXM.escapeHtml(s.upiId) + '">Copy</button></div></div>';
      }
      if (s.paymentDisplayName) {
        html += '<p class="mb-2 small text-secondary">Pay to: <b class="text-white">' + window.BXM.escapeHtml(s.paymentDisplayName) + '</b></p>';
      }
      if (s.instructions) {
        html += '<div class="bxm-instruction-box">' + window.BXM.escapeHtml(s.instructions) + '</div>';
      }
      host.innerHTML = html;
      updatePayable();
    });
  }

  function updatePayable() {
    var el = document.getElementById('payableAmount');
    if (el) el.textContent = window.BXM.money(Math.max(0, subtotal - appliedDiscount));
  }

  document.getElementById('screenshotInput').addEventListener('change', function (e) {
    var file = e.target.files[0];
    var preview = document.getElementById('screenshotPreview');
    if (!file) { preview.hidden = true; return; }
    var reader = new FileReader();
    reader.onload = function (ev) { preview.src = ev.target.result; preview.hidden = false; };
    reader.readAsDataURL(file);
  });

  document.getElementById('paymentForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var file = document.getElementById('screenshotInput').files[0];
    var txId = document.getElementById('transactionId').value.trim();
    if (!file) { window.BXM.toast('Please upload the payment screenshot', 'warning'); return; }
    if (!txId) { window.BXM.toast('Please enter the transaction ID', 'warning'); return; }
    if (!cartIds().length) { window.BXM.toast('Your cart is empty', 'warning'); return; }
    var btn = document.getElementById('submitPaymentBtn');
    btn.disabled = true;
    window.BXM.loader(true);
    window.BXM.uploadImage(file, 'payment').then(function (up) {
      if (!up.ok) { throw new Error(up.error || 'Upload failed'); }
      return window.BXM.apiFetch('create-order.php', { method: 'POST', json: {
        transactionId: txId,
        paymentScreenshotUrl: up.url,
        couponCode: appliedCoupon
      }});
    }).then(function (res) {
      window.BXM.loader(false);
      btn.disabled = false;
      if (!res.ok) { window.BXM.toast(res.error || 'Could not submit payment', 'danger'); return; }
      try { sessionStorage.removeItem('bxmCheckout'); } catch (err) {}
      window.BXM.toast('Payment submitted successfully', 'success');
      setTimeout(function () {
        window.location.href = window.BXM.url('order-details.php?id=' + encodeURIComponent(res.orderId) + '&new=1');
      }, 900);
    }).catch(function (err) {
      window.BXM.loader(false);
      btn.disabled = false;
      window.BXM.toast(err.message || 'Something went wrong', 'danger');
    });
  });

  document.addEventListener('bxm:cart', function () {
    cartReady = true;
    render();
  });

  window.BXM.onAuth(function (user) {
    if (!window.BXM.firebaseReady || !user) return;
    if (window.BXM.state.cart && Object.keys(window.BXM.state.cart).length) cartReady = true;
    window.BXM.db.ref('products').once('value').then(function (snap) {
      snap.forEach(function (c) { products[c.key] = c.val(); });
      productsReady = true;
      render();
      loadPaymentInfo();
    });
  });
})();
</script>
HTML;
include __DIR__ . '/includes/footer.php';
?>
