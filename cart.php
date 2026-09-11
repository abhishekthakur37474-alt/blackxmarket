<?php
require_once __DIR__ . '/includes/auth-check.php';
$pageTitle = 'Cart';
$pageDescription = 'Review the digital products in your cart.';
$noIndex = true;
$activePage = '';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container" id="bxmConfigNotice"></div>

<section class="bxm-section-sm">
  <div class="container">
    <h1 class="bxm-section-title mb-4">Your Cart</h1>
    <div class="row g-4">
      <div class="col-lg-8">
        <div id="cartItems">
          <div class="bxm-skeleton mb-3" style="height:110px"></div>
          <div class="bxm-skeleton" style="height:110px"></div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="bxm-card p-4 bxm-sticky-summary">
          <h6 class="mb-3">Order Summary</h6>
          <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Subtotal</span><span id="cartSubtotal">₹0.00</span></div>
          <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Discount</span><span class="text-success" id="cartSavings">-₹0.00</span></div>
          <hr class="bxm-divider my-3">
          <div class="d-flex justify-content-between mb-4"><span class="fw-semibold">Total</span><span class="fw-bold" id="cartTotal">₹0.00</span></div>
          <a href="checkout.php" class="bxm-btn bxm-btn-primary w-100" id="checkoutBtn"><i class="bi bi-arrow-right"></i> Proceed to Checkout</a>
          <a href="products.php" class="bxm-btn bxm-btn-ghost w-100 mt-2">Continue Shopping</a>
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

  function itemHTML(productId, entry, product) {
    if (!product) return '';
    var qty = Number(entry.quantity) || 1;
    var price = window.BXM.toNumber(product.discountedPrice || product.originalPrice);
    return '<div class="bxm-card p-3 mb-3"><div class="d-flex gap-3 align-items-center">' +
      '<img src="' + window.BXM.escapeHtml(product.thumbnailUrl || '') + '" class="bxm-table-thumb" style="width:72px;height:72px" alt="">' +
      '<div class="flex-grow-1 min-w-0">' +
      '<a href="' + window.BXM.url('product-details.php?id=' + encodeURIComponent(productId)) + '" class="fw-semibold text-white d-block text-truncate">' + window.BXM.escapeHtml(product.title || 'Product') + '</a>' +
      '<div class="small text-muted mb-1">' + window.BXM.escapeHtml(product.category || '') + '</div>' +
      '<div class="fw-bold">' + window.BXM.money(price) + '</div>' +
      '</div>' +
      '<div class="d-flex flex-column align-items-end gap-2">' +
      '<div class="d-flex align-items-center gap-1">' +
      '<button class="bxm-icon-btn" style="width:30px;height:30px;font-size:.8rem" data-cart-dec="' + productId + '"><i class="bi bi-dash"></i></button>' +
      '<span class="px-2">' + qty + '</span>' +
      '<button class="bxm-icon-btn" style="width:30px;height:30px;font-size:.8rem" data-cart-inc="' + productId + '"><i class="bi bi-plus"></i></button>' +
      '</div>' +
      '<button class="bxm-copy-btn" data-cart-remove="' + productId + '"><i class="bi bi-trash"></i> Remove</button>' +
      '</div></div></div>';
  }

  function render() {
    if (!loaded) return;
    var cart = window.BXM.state.cart || {};
    var host = document.getElementById('cartItems');
    var ids = Object.keys(cart);
    if (!ids.length) {
      host.innerHTML = '<div class="bxm-state-box text-center"><i class="bi bi-bag bxm-state-icon"></i><h5>Your cart is empty</h5><p class="text-secondary mb-3">Browse our digital products.</p><a href="products.php" class="bxm-btn bxm-btn-primary">Shop Now</a></div>';
      document.getElementById('checkoutBtn').classList.add('disabled');
      document.getElementById('cartSubtotal').textContent = window.BXM.money(0);
      document.getElementById('cartSavings').textContent = '-' + window.BXM.money(0);
      document.getElementById('cartTotal').textContent = window.BXM.money(0);
      return;
    }
    document.getElementById('checkoutBtn').classList.remove('disabled');
    var html = '';
    var subtotal = 0;
    var originalTotal = 0;
    ids.forEach(function (id) {
      if (!products[id]) return;
      var entry = cart[id];
      var qty = Number(entry.quantity) || 1;
      subtotal += window.BXM.toNumber(products[id].discountedPrice || products[id].originalPrice) * qty;
      originalTotal += window.BXM.toNumber(products[id].originalPrice) * qty;
      html += itemHTML(id, entry, products[id]);
    });
    host.innerHTML = html || '<div class="bxm-state-box text-center"><p class="text-secondary mb-0">No available items.</p></div>';
    document.getElementById('cartSubtotal').textContent = window.BXM.money(subtotal);
    document.getElementById('cartSavings').textContent = '-' + window.BXM.money(Math.max(0, originalTotal - subtotal));
    document.getElementById('cartTotal').textContent = window.BXM.money(subtotal);
  }

  document.addEventListener('click', function (e) {
    var inc = e.target.closest('[data-cart-inc]');
    var dec = e.target.closest('[data-cart-dec]');
    var rem = e.target.closest('[data-cart-remove]');
    if (inc || dec) {
      var id = (inc || dec).getAttribute(inc ? 'data-cart-inc' : 'data-cart-dec');
      var entry = (window.BXM.state.cart || {})[id] || {};
      var qty = Number(entry.quantity) || 1;
      window.BXM.setCartQuantity(id, inc ? qty + 1 : qty - 1);
    }
    if (rem) {
      var rid = rem.getAttribute('data-cart-remove');
      window.BXM.confirmDialog('Remove this item from your cart?', { confirmText: 'Remove', danger: true }).then(function (ok) {
        if (ok) window.BXM.removeFromCart(rid);
      });
    }
  });

  document.addEventListener('bxm:cart', render);

  window.BXM.onAuth(function (user) {
    if (!window.BXM.firebaseReady || !user) return;
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
