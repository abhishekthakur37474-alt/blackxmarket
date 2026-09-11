<?php
$productId = isset($_GET['id']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['id']) : '';
$pageTitle = 'Product Details';
$pageDescription = 'View product details, pricing and delivery information.';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container" id="bxmConfigNotice"></div>

<section class="bxm-section-sm">
  <div class="container">
    <nav class="mb-3 small">
      <a href="index.php" class="bxm-link-muted">Home</a>
      <span class="text-muted mx-1">/</span>
      <a href="products.php" class="bxm-link-muted">Products</a>
      <span class="text-muted mx-1">/</span>
      <span class="text-secondary" id="crumbTitle">Details</span>
    </nav>

    <div id="productDetail">
      <div class="row g-4">
        <div class="col-lg-5"><div class="bxm-skeleton" style="height:400px"></div></div>
        <div class="col-lg-7"><div class="bxm-skeleton" style="height:400px"></div></div>
      </div>
    </div>

    <div id="relatedSection" class="mt-5" hidden>
      <h2 class="bxm-section-title mb-4">Related Products</h2>
      <div class="row g-3" id="relatedProducts"></div>
    </div>
  </div>
</section>
<?php
$inlineScript = '<script>window.BXM_PRODUCT_ID = ' . json_encode($productId) . ';</script>' . <<<'HTML'
<script>
(function () {
  var productId = window.BXM_PRODUCT_ID || '';
  var current = null;

  function stars(rating) {
    var out = '';
    for (var i = 1; i <= 5; i++) out += '<i class="bi ' + (i <= rating ? 'bi-star-fill text-warning' : 'bi-star text-secondary') + '"></i>';
    return out;
  }

  function render(product, reviews) {
    current = product;
    document.getElementById('crumbTitle').textContent = product.title || 'Details';
    document.title = (product.title || 'Product') + ' | BLACK X MARKET';

    var percent = window.BXM.discountPercent(product);
    var outOfStock = product.status === 'out_of_stock';
    var avg = 0;
    if (reviews.length) {
      avg = Math.round(reviews.reduce(function (s, r) { return s + (Number(r.rating) || 0); }, 0) / reviews.length);
    }

    var additional = '';
    if (product.additionalDetails && typeof product.additionalDetails === 'object') {
      var fields = Object.keys(product.additionalDetails).map(function (k) { return product.additionalDetails[k]; });
      fields.sort(function (a, b) { return window.BXM.toNumber(a.order) - window.BXM.toNumber(b.order); });
      if (fields.length) {
        additional = '<div class="bxm-card p-3 mt-4"><h6 class="mb-3">Additional Details</h6>' +
          fields.map(function (f) {
            return '<div class="bxm-delivery-row"><span class="bxm-delivery-key">' + window.BXM.escapeHtml(f.name || '') + '</span><span class="bxm-delivery-val">' + window.BXM.escapeHtml(f.value || '') + '</span></div>';
          }).join('') + '</div>';
      }
    }

    var reviewBlock = '';
    if (reviews.length) {
      reviewBlock = '<div class="mt-5"><h6 class="mb-3">Customer Reviews</h6>' + reviews.slice(0, 5).map(function (r) {
        return '<div class="bxm-card p-3 mb-2"><div class="mb-1">' + stars(Number(r.rating) || 0) + '</div>' +
          '<p class="text-secondary mb-1">' + window.BXM.escapeHtml(r.comment || '') + '</p>' +
          '<div class="small text-muted">- ' + window.BXM.escapeHtml(r.userName || 'Customer') + '</div></div>';
      }).join('') + '</div>';
    }

    var html = '<div class="row g-4">';
    html += '<div class="col-lg-5"><div class="bxm-product-thumb" style="aspect-ratio:4/3;border-radius:16px;border:1px solid var(--bxm-border)">';
    if (percent > 0) html += '<span class="bxm-discount-badge">-' + percent + '%</span>';
    html += '<img src="' + window.BXM.escapeHtml(product.thumbnailUrl || '') + '" alt="' + window.BXM.escapeHtml(product.title || '') + '" loading="lazy"></div></div>';
    html += '<div class="col-lg-7">';
    html += '<span class="bxm-badge mb-2">' + window.BXM.escapeHtml(product.category || 'Digital') + '</span>';
    html += '<h1 class="h2 mb-2">' + window.BXM.escapeHtml(product.title || 'Untitled') + '</h1>';
    html += '<div class="mb-3">' + stars(avg) + ' <span class="text-secondary small ms-1">' + (reviews.length ? reviews.length + ' review(s)' : 'No reviews yet') + '</span></div>';
    html += '<div class="mb-4"><span class="bxm-price-current" style="font-size:1.8rem">' + window.BXM.money(product.discountedPrice || product.originalPrice) + '</span>';
    if (window.BXM.toNumber(product.originalPrice) > window.BXM.toNumber(product.discountedPrice)) {
      html += '<span class="bxm-price-original" style="font-size:1rem">' + window.BXM.money(product.originalPrice) + '</span>';
    }
    html += '</div>';
    html += '<div class="d-flex flex-wrap gap-2 mb-4">';
    html += '<button class="bxm-btn bxm-btn-primary' + (outOfStock ? ' disabled' : '') + '" data-bxm-add-cart="' + window.BXM.escapeHtml(productId) + '"><i class="bi bi-bag-plus"></i> Add to Cart</button>';
    html += '<button class="bxm-btn bxm-btn-outline' + (outOfStock ? ' disabled' : '') + '" id="buyNowBtn"><i class="bi bi-lightning-charge"></i> Buy Now</button>';
    html += '<button class="bxm-btn bxm-btn-outline" data-bxm-wish="' + window.BXM.escapeHtml(productId) + '"><i class="bi bi-heart"></i> Wishlist</button>';
    html += '</div>';
    html += '<div class="bxm-card p-3"><h6 class="mb-2">Description</h6><p class="text-secondary mb-0" style="white-space:pre-line">' + window.BXM.escapeHtml(product.description || 'No description provided.') + '</p></div>';
    html += additional;
    html += reviewBlock;
    html += '</div></div>';

    document.getElementById('productDetail').innerHTML = html;

    var buy = document.getElementById('buyNowBtn');
    if (buy) {
      buy.addEventListener('click', function () {
        if (!window.BXM.requireLogin()) return;
        window.BXM.addToCart(productId);
        setTimeout(function () { window.location.href = window.BXM.url('cart.php'); }, 600);
      });
    }
  }

  window.BXM.onAuth(function () {
    if (!window.BXM.firebaseReady) return;
    if (!productId) {
      document.getElementById('productDetail').innerHTML = '<div class="bxm-state-box text-center"><i class="bi bi-box bxm-state-icon"></i><h5>Product not found</h5><p class="text-secondary mb-3">The product you are looking for does not exist.</p><a href="products.php" class="bxm-btn bxm-btn-primary">Browse Products</a></div>';
      return;
    }
    window.BXM.db.ref('products/' + productId).once('value').then(function (snap) {
      var product = snap.val();
      if (!product) {
        document.getElementById('productDetail').innerHTML = '<div class="bxm-state-box text-center"><i class="bi bi-box bxm-state-icon"></i><h5>Product not found</h5><p class="text-secondary mb-3">The product you are looking for does not exist.</p><a href="products.php" class="bxm-btn bxm-btn-primary">Browse Products</a></div>';
        return;
      }
      if (product.status && product.status !== 'active' && product.status !== 'out_of_stock') {
        document.getElementById('productDetail').innerHTML = '<div class="bxm-state-box text-center"><i class="bi bi-box bxm-state-icon"></i><h5>Product not found</h5><p class="text-secondary mb-3">This product is no longer available.</p><a href="products.php" class="bxm-btn bxm-btn-primary">Browse Products</a></div>';
        return;
      }
      product.id = productId;
      window.BXM.db.ref('reviews').once('value').then(function (rsnap) {
        var reviews = [];
        rsnap.forEach(function (c) {
          var r = c.val() || {};
          if (r.productId === productId && r.status === 'approved') reviews.push(r);
        });
        render(product, reviews);
      });
      window.BXM.db.ref('products').once('value').then(function (all) {
        var related = [];
        all.forEach(function (c) {
          var p = c.val() || {};
          if (c.key !== productId && p.status === 'active' && p.category === product.category) {
            p.id = c.key;
            related.push(p);
          }
        });
        related = related.slice(0, 4);
        if (related.length) {
          document.getElementById('relatedProducts').innerHTML = related.map(function (p) {
            return '<div class="col-6 col-md-4 col-lg-3">' + window.BXM.productCardHTML(p) + '</div>';
          }).join('');
          document.getElementById('relatedSection').hidden = false;
        }
      });
    });
  });
})();
</script>
HTML;
include __DIR__ . '/includes/footer.php';
?>
