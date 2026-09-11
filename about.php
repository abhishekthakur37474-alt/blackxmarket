<?php
$pageTitle = 'About Us';
$pageDescription = 'Learn about BLACK X MARKET, a premium marketplace for lawful digital gaming products.';
$activePage = 'about';
require_once __DIR__ . '/includes/header.php';
?>
<section class="bxm-section">
  <div class="container">
    <div class="row g-5 align-items-center">
      <div class="col-lg-6">
        <span class="bxm-badge mb-3">About BLACK X MARKET</span>
        <h1 class="bxm-section-title mb-3">A premium marketplace for digital gaming products.</h1>
        <p class="text-secondary">BLACK X MARKET connects gamers with lawful, authorized digital products &mdash; game-related digital items, legitimate keys and codes, digital guides, presets, assets and more.</p>
        <p class="text-secondary">Our mission is simple: deliver a fast, secure and transparent experience. Every order is manually verified and every delivery is protected inside your account.</p>
        <a href="products.php" class="bxm-btn bxm-btn-primary mt-2">Browse Products</a>
      </div>
      <div class="col-lg-6">
        <div class="row g-3">
          <div class="col-6"><div class="bxm-feature-card"><div class="bxm-feature-icon"><i class="bi bi-lightning-charge"></i></div><h6 class="mb-1">Instant Delivery</h6><p class="text-secondary small mb-0">Access after approval</p></div></div>
          <div class="col-6"><div class="bxm-feature-card"><div class="bxm-feature-icon"><i class="bi bi-shield-check"></i></div><h6 class="mb-1">Secure</h6><p class="text-secondary small mb-0">Protected checkout</p></div></div>
          <div class="col-6"><div class="bxm-feature-card"><div class="bxm-feature-icon"><i class="bi bi-patch-check"></i></div><h6 class="mb-1">Verified</h6><p class="text-secondary small mb-0">Lawful products only</p></div></div>
          <div class="col-6"><div class="bxm-feature-card"><div class="bxm-feature-icon"><i class="bi bi-headset"></i></div><h6 class="mb-1">Support</h6><p class="text-secondary small mb-0">We are here to help</p></div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="bxm-section-sm">
  <div class="container">
    <div class="bxm-card p-4 p-md-5">
      <h2 class="bxm-section-title mb-4">What we stand for</h2>
      <div class="row g-4">
        <div class="col-md-4"><h6><i class="bi bi-check-circle text-success me-2"></i>Legitimate products</h6><p class="text-secondary small mb-0">We allow only lawful, authorized digital goods. Unauthorized accounts, stolen credentials and illegally obtained goods are not permitted.</p></div>
        <div class="col-md-4"><h6><i class="bi bi-check-circle text-success me-2"></i>Transparency</h6><p class="text-secondary small mb-0">Every order shows its status, payment verification and delivery information clearly.</p></div>
        <div class="col-md-4"><h6><i class="bi bi-check-circle text-success me-2"></i>Fair pricing</h6><p class="text-secondary small mb-0">Discounted digital products and coupons, with the final amount always calculated securely.</p></div>
      </div>
    </div>
  </div>
</section>
<?php
include __DIR__ . '/includes/footer.php';
?>
