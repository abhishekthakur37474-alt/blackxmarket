<?php
$pageTitle = 'Home';
$pageDescription = 'Discover premium digital gaming products at BLACK X MARKET. Verified products, secure checkout and instant digital delivery.';
$activePage = 'home';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container" id="bxmConfigNotice"></div>

<section class="bxm-hero">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <span class="bxm-badge mb-3"><i class="bi bi-lightning-charge-fill"></i> Instant Digital Delivery</span>
        <h1 class="bxm-hero-title mb-3">Digital Gaming<br>Stuff, <span class="text-secondary">Delivered.</span></h1>
        <p class="bxm-hero-sub mb-4">Discover premium digital products at BLACK X MARKET. Verified sellers, secure checkout and automated delivery after approval.</p>
        <div class="d-flex flex-wrap gap-2">
          <a href="products.php" class="bxm-btn bxm-btn-primary"><i class="bi bi-bag"></i> Shop Now</a>
          <a href="products.php" class="bxm-btn bxm-btn-outline"><i class="bi bi-grid"></i> Explore Products</a>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="bxm-card p-4">
          <div class="row g-3 text-center">
            <div class="col-6">
              <div class="bxm-stat-value" id="heroProducts">0+</div>
              <div class="bxm-stat-label">Products</div>
            </div>
            <div class="col-6">
              <div class="bxm-stat-value" id="heroOrders">0+</div>
              <div class="bxm-stat-label">Delivered</div>
            </div>
          </div>
          <hr class="bxm-divider my-4">
          <ul class="list-unstyled mb-0 d-grid gap-2">
            <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i><span class="text-secondary">Verified digital products</span></li>
            <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i><span class="text-secondary">Manual payment verification</span></li>
            <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i><span class="text-secondary">Secure order delivery card</span></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="bxm-section-sm" id="carouselSection" hidden>
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-3">
      <div>
        <h2 class="bxm-section-title">Featured</h2>
        <p class="bxm-section-sub mb-0">Handpicked offers from the marketplace.</p>
      </div>
    </div>
    <div id="homeCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner" id="carouselInner"></div>
      <div class="d-flex gap-2 mt-3">
        <button class="bxm-icon-btn" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev"><i class="bi bi-arrow-left"></i></button>
        <button class="bxm-icon-btn" type="button" data-bs-target="#homeCarousel" data-bs-slide="next"><i class="bi bi-arrow-right"></i></button>
      </div>
    </div>
  </div>
</section>

<section class="bxm-section">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4">
      <div>
        <h2 class="bxm-section-title">Featured Products</h2>
        <p class="bxm-section-sub mb-0">Top digital products ready for instant delivery.</p>
      </div>
      <a href="products.php" class="bxm-btn bxm-btn-outline bxm-btn-sm d-none d-sm-inline-flex">View All</a>
    </div>
    <div class="row g-3" id="featuredProducts">
      <?php for ($i = 0; $i < 4; $i++): ?>
        <div class="col-6 col-md-4 col-lg-3"><div class="bxm-skeleton" style="height:280px"></div></div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<section class="bxm-section-sm">
  <div class="container">
    <div class="row g-3">
      <div class="col-6 col-lg-3">
        <div class="bxm-feature-card">
          <div class="bxm-feature-icon"><i class="bi bi-lightning-charge"></i></div>
          <h6>Fast Digital Delivery</h6>
          <p class="text-secondary small mb-0">Get access right after payment approval.</p>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="bxm-feature-card">
          <div class="bxm-feature-icon"><i class="bi bi-shield-check"></i></div>
          <h6>Secure Checkout</h6>
          <p class="text-secondary small mb-0">Verified payments and protected orders.</p>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="bxm-feature-card">
          <div class="bxm-feature-icon"><i class="bi bi-patch-check"></i></div>
          <h6>Verified Products</h6>
          <p class="text-secondary small mb-0">Only lawful, authorized digital goods.</p>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="bxm-feature-card">
          <div class="bxm-feature-icon"><i class="bi bi-headset"></i></div>
          <h6>Customer Support</h6>
          <p class="text-secondary small mb-0">We are here to help with every order.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="bxm-section">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4">
      <div>
        <h2 class="bxm-section-title">What Customers Say</h2>
        <p class="bxm-section-sub mb-0">Verified reviews from real buyers.</p>
      </div>
      <a href="reviews.php" class="bxm-btn bxm-btn-outline bxm-btn-sm d-none d-sm-inline-flex">All Reviews</a>
    </div>
    <div class="row g-3" id="homeReviews">
      <div class="col-md-4"><div class="bxm-skeleton" style="height:150px"></div></div>
      <div class="col-md-4"><div class="bxm-skeleton" style="height:150px"></div></div>
      <div class="col-md-4"><div class="bxm-skeleton" style="height:150px"></div></div>
    </div>
  </div>
</section>

<section class="bxm-section-sm">
  <div class="container">
    <div class="bxm-cta">
      <h2 class="mb-2">Ready to get started?</h2>
      <p class="text-secondary mb-4">Browse our digital collection and get instant access after approval.</p>
      <a href="products.php" class="bxm-btn bxm-btn-primary"><i class="bi bi-bag"></i> Shop Products</a>
    </div>
  </div>
</section>
<?php
$inlineScript = <<<'HTML'
<script>
(function () {
  function stars(rating) {
    var out = '';
    for (var i = 1; i <= 5; i++) {
      out += '<i class="bi ' + (i <= rating ? 'bi-star-fill text-warning' : 'bi-star text-secondary') + '"></i>';
    }
    return out;
  }

  window.BXM.onAuth(function () {
    if (!window.BXM.firebaseReady) return;
    var db = window.BXM.db;

    db.ref('products').orderByChild('createdAt').limitToLast(8).once('value').then(function (snap) {
      var products = [];
      snap.forEach(function (child) {
        var p = child.val() || {};
        p.id = child.key;
        if (p.status !== 'inactive') products.push(p);
      });
      products.reverse();
      window.BXM.renderProducts(document.getElementById('featuredProducts'), products.slice(0, 8));
      document.getElementById('heroProducts').textContent = products.length + '+';
    });

    db.ref('products').once('value').then(function (snap) {
      var active = 0;
      snap.forEach(function (c) { if ((c.val() || {}).status === 'active') active++; });
      document.getElementById('heroProducts').textContent = active + '+';
    });

    db.ref('carousels').orderByChild('order').once('value').then(function (snap) {
      var slides = [];
      snap.forEach(function (child) {
        var c = child.val() || {};
        if (c.status === 'active') slides.push(c);
      });
      if (!slides.length) return;
      var inner = document.getElementById('carouselInner');
      inner.innerHTML = slides.map(function (s, i) {
        return '<div class="carousel-item' + (i === 0 ? ' active' : '') + '">' +
          '<div class="bxm-carousel-slide"><img src="' + window.BXM.escapeHtml(s.imageUrl || '') + '" alt="' + window.BXM.escapeHtml(s.title || 'Featured') + '">' +
          '<div class="bxm-carousel-caption"><h3 class="mb-1">' + window.BXM.escapeHtml(s.title || '') + '</h3>' +
          (s.description ? '<p class="text-secondary mb-3">' + window.BXM.escapeHtml(s.description) + '</p>' : '') +
          (s.link ? '<a href="' + window.BXM.escapeHtml(s.link) + '" class="bxm-btn bxm-btn-primary">' + window.BXM.escapeHtml(s.buttonText || 'Explore Now') + '</a>' : '') +
          '</div></div></div>';
      }).join('');
      document.getElementById('carouselSection').hidden = false;
    });

    db.ref('reviews').once('value').then(function (snap) {
      var reviews = [];
      snap.forEach(function (child) {
        var r = child.val() || {};
        if (r.status === 'approved') reviews.push(r);
      });
      var host = document.getElementById('homeReviews');
      reviews = reviews.slice(-3).reverse();
      if (!reviews.length) {
        host.innerHTML = '<div class="col-12"><div class="bxm-state-box text-center"><i class="bi bi-chat-quote bxm-state-icon"></i><p class="text-secondary mb-0">No reviews yet.</p></div></div>';
        return;
      }
      host.innerHTML = reviews.map(function (r) {
        return '<div class="col-md-4"><div class="bxm-feature-card h-100">' +
          '<div class="mb-2">' + stars(Number(r.rating) || 0) + '</div>' +
          '<p class="text-secondary">' + window.BXM.escapeHtml(r.comment || '') + '</p>' +
          '<div class="small text-muted">- ' + window.BXM.escapeHtml(r.userName || 'Customer') + '</div>' +
          '</div></div>';
      }).join('');
    });
  });
})();
</script>
HTML;
include __DIR__ . '/includes/footer.php';
?>
