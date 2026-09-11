<?php
$inlineScript = isset($inlineScript) ? $inlineScript : '';
$footerLinks = [
    'Quick Links' => [
        ['Home', bxm_url('index.php')],
        ['Products', bxm_url('products.php')],
        ['About Us', bxm_url('about.php')],
        ['Reviews', bxm_url('reviews.php')],
        ['Customer Support', bxm_url('support.php')],
    ],
    'Account' => [
        ['My Profile', bxm_url('profile.php')],
        ['My Orders', bxm_url('orders.php')],
        ['Wishlist', bxm_url('wishlist.php')],
        ['Cart', bxm_url('cart.php')],
    ],
    'Support' => [
        ['Customer Support', bxm_url('support.php')],
        ['Terms & Conditions', bxm_url('page.php?slug=terms')],
        ['Privacy Policy', bxm_url('page.php?slug=privacy')],
        ['Refund Policy', bxm_url('page.php?slug=refund')],
    ],
];
?>
</main>
<footer class="bxm-footer">
  <div class="container">
    <div class="row gy-4">
      <div class="col-lg-4">
        <div class="bxm-brand mb-3">
          <img src="<?= bxm_url('assets/images/logo/logo.jpeg') ?>" alt="BLACK X MARKET" width="36" height="36" class="bxm-brand-logo">
          <span>BLACK<span class="bxm-brand-accent">X</span>MARKET</span>
        </div>
        <p class="text-secondary bxm-footer-desc">Premium digital gaming products, delivered instantly. Verified products, secure checkout and fast digital delivery.</p>
        <div class="d-flex gap-2">
          <a href="<?= bxm_url('products.php') ?>" class="bxm-btn bxm-btn-primary">Shop Now</a>
          <a href="<?= bxm_url('support.php') ?>" class="bxm-btn bxm-btn-outline">Support</a>
        </div>
      </div>
      <?php foreach ($footerLinks as $heading => $links): ?>
      <div class="col-6 col-lg-2 mb-3 mb-lg-0">
        <h6 class="bxm-footer-heading"><?= bxm_e($heading) ?></h6>
        <ul class="list-unstyled bxm-footer-links">
          <?php foreach ($links as $link): ?>
          <li><a href="<?= bxm_e($link[1]) ?>"><?= bxm_e($link[0]) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endforeach; ?>
      <div class="col-lg-2">
        <h6 class="bxm-footer-heading">Stay Secure</h6>
        <ul class="list-unstyled bxm-footer-links">
          <li><i class="bi bi-shield-check me-2"></i>Secure Checkout</li>
          <li><i class="bi bi-lightning-charge me-2"></i>Instant Delivery</li>
          <li><i class="bi bi-patch-check me-2"></i>Verified Products</li>
        </ul>
      </div>
    </div>
    <hr class="bxm-footer-divider">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
      <p class="mb-0 text-secondary small">&copy; <?= bxm_e(bxm_site('year')) ?> <?= bxm_e(bxm_site('name')) ?>. All Rights Reserved.</p>
      <p class="mb-0 text-secondary small">Digital products only &mdash; no physical shipping.</p>
    </div>
  </div>
</footer>

<div class="bxm-loader" id="bxmLoader" aria-hidden="true">
  <div class="bxm-spinner"></div>
</div>

<script src="<?= bxm_url('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= bxm_url('assets/vendor/firebase/firebase-app-compat.js') ?>"></script>
<script src="<?= bxm_url('assets/vendor/firebase/firebase-auth-compat.js') ?>"></script>
<script src="<?= bxm_url('assets/vendor/firebase/firebase-database-compat.js') ?>"></script>
<script src="<?= bxm_url('firebase/firebase-init.js') ?>"></script>
<script src="<?= bxm_url('assets/js/app.js') ?>"></script>
<?= $inlineScript ?>
</body>
</html>
