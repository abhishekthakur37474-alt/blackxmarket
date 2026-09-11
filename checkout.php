<?php
require_once __DIR__ . '/includes/auth-check.php';
$pageTitle = 'Checkout';
$pageDescription = 'Submit your payment for verification.';
$noIndex = true;
require_once __DIR__ . '/includes/header.php';
?>
<div class="container" id="bxmConfigNotice"></div>

<section class="bxm-section-sm">
  <div class="container">
    <h1 class="bxm-section-title mb-4">Checkout</h1>
    <div class="row g-4">
      <div class="col-lg-7">
        <div class="bxm-card p-4 mb-4">
          <h6 class="mb-3">Order Items</h6>
          <div id="checkoutItems"><div class="bxm-skeleton" style="height:80px"></div></div>
        </div>

        <div class="bxm-card p-4">
          <p class="text-secondary small mb-3">Review your items and apply a coupon if you have one. Payment instructions will be shown on the next page.</p>
          <button type="button" class="bxm-btn bxm-btn-primary w-100" id="goPaymentBtn"><i class="bi bi-credit-card"></i> Proceed to Payment</button>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="bxm-card p-4 bxm-sticky-summary">
          <h6 class="mb-3">Order Summary</h6>
          <div id="summaryItems" class="mb-3"></div>
          <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Subtotal</span><span id="summarySubtotal">₹0.00</span></div>
          <div class="d-flex justify-content-between mb-3"><span class="text-secondary">Coupon Discount</span><span class="text-success" id="summaryDiscount">-₹0.00</span></div>

          <label class="form-label">Coupon Code</label>
          <div class="input-group mb-2">
            <input type="text" class="form-control" id="couponInput" placeholder="GAMING20">
            <button class="bxm-btn bxm-btn-outline" type="button" id="applyCouponBtn">Apply</button>
          </div>
          <div id="couponMessage" class="small mb-3"></div>

          <hr class="bxm-divider my-3">
          <div class="d-flex justify-content-between mb-2"><span class="fw-semibold">Final Amount</span><span class="fw-bold" id="summaryTotal">₹0.00</span></div>
          <p class="text-muted small mb-0">You will pay this amount on the next page.</p>
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
  var loaded = false;
  var appliedCoupon = '';
  var appliedDiscount = 0;
  var subtotal = 0;

  function computeSubtotal() {
    var cart = window.BXM.state.cart || {};
    var total = 0;
    var rows = '';
    Object.keys(cart).forEach(function (id) {
      var p = products[id];
      if (!p) return;
      var qty = Number(cart[id].quantity) || 1;
      var price = window.BXM.toNumber(p.discountedPrice || p.originalPrice);
      total += price * qty;
      rows += '<div class="d-flex justify-content-between align-items-center mb-2 small">' +
        '<span class="text-secondary text-truncate me-2">' + window.BXM.escapeHtml(p.title || 'Product') + ' x' + qty + '</span>' +
        '<span>' + window.BXM.money(price * qty) + '</span></div>';
    });
    document.getElementById('summaryItems').innerHTML = rows;
    return total;
  }

  var cartReady = false;

  function render() {
    if (!loaded) return;
    var cart = window.BXM.state.cart || {};
    var ids = Object.keys(cart).filter(function (id) { return products[id]; });
    if (cartReady && !ids.length) {
      document.getElementById('checkoutItems').innerHTML = '<div class="bxm-state-box text-center"><i class="bi bi-bag bxm-state-icon"></i><h5>Your cart is empty</h5><a href="cart.php" class="bxm-btn bxm-btn-primary mt-2">Go to Cart</a></div>';
      return;
    }
    if (!ids.length) return;
    var itemsHtml = '';
    ids.forEach(function (id) {
      var p = products[id];
      var qty = Number(cart[id].quantity) || 1;
      itemsHtml += '<div class="d-flex gap-3 align-items-center mb-3">' +
        '<img src="' + window.BXM.escapeHtml(p.thumbnailUrl || '') + '" class="bxm-table-thumb" alt="">' +
        '<div class="flex-grow-1"><div class="fw-semibold">' + window.BXM.escapeHtml(p.title || '') + '</div>' +
        '<div class="small text-muted">Qty ' + qty + ' &middot; ' + window.BXM.money(p.discountedPrice || p.originalPrice) + '</div></div>' +
        '<div class="fw-bold">' + window.BXM.money(window.BXM.toNumber(p.discountedPrice || p.originalPrice) * qty) + '</div></div>';
    });
    document.getElementById('checkoutItems').innerHTML = itemsHtml;

    subtotal = computeSubtotal();
    if (appliedCoupon) {
      var result = appliedDiscount;
      document.getElementById('couponMessage').innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Coupon <b>' + window.BXM.escapeHtml(appliedCoupon) + '</b> applied. You saved ' + window.BXM.money(result) + '</span> <button type="button" class="bxm-copy-btn ms-1" id="removeCouponBtn">Remove</button>';
    }
    document.getElementById('summarySubtotal').textContent = window.BXM.money(subtotal);
    document.getElementById('summaryDiscount').textContent = '-' + window.BXM.money(appliedDiscount);
    document.getElementById('summaryTotal').textContent = window.BXM.money(Math.max(0, subtotal - appliedDiscount));
  }

  document.getElementById('applyCouponBtn').addEventListener('click', function () {
    var code = document.getElementById('couponInput').value.trim();
    var msg = document.getElementById('couponMessage');
    if (!code) { msg.innerHTML = '<span class="text-warning">Enter a coupon code.</span>'; return; }
    window.BXM.loader(true);
    window.BXM.apiFetch('validate-coupon.php', { method: 'POST', json: { code: code, cartAmount: subtotal } })
      .then(function (res) {
        window.BXM.loader(false);
        if (!res.ok) {
          appliedCoupon = ''; appliedDiscount = 0;
          msg.innerHTML = '<span class="text-danger">' + window.BXM.escapeHtml(res.error || 'Invalid coupon') + '</span>';
          render();
          return;
        }
        appliedCoupon = res.code;
        appliedDiscount = Number(res.discount) || 0;
        render();
        window.BXM.toast('Coupon applied successfully', 'success');
      })
      .catch(function () { window.BXM.loader(false); msg.innerHTML = '<span class="text-danger">Could not apply coupon.</span>'; });
  });

  document.addEventListener('click', function (e) {
    if (e.target.closest('#removeCouponBtn')) {
      appliedCoupon = ''; appliedDiscount = 0;
      document.getElementById('couponInput').value = '';
      document.getElementById('couponMessage').innerHTML = '';
      render();
    }
  });

  document.getElementById('goPaymentBtn').addEventListener('click', function () {
    var cart = window.BXM.state.cart || {};
    var ids = Object.keys(cart).filter(function (id) { return products[id]; });
    if (!ids.length) {
      window.BXM.toast('Your cart is empty', 'warning');
      return;
    }
    try {
      sessionStorage.setItem('bxmCheckout', JSON.stringify({ coupon: appliedCoupon, discount: appliedDiscount }));
    } catch (err) {}
    window.location.href = window.BXM.url('payment.php');
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
      loaded = true;
      render();
    });
  });
})();
</script>
HTML;
include __DIR__ . '/includes/footer.php';
?>
