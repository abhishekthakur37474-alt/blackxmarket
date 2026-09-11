<?php
http_response_code(404);
$pageTitle = 'Page Not Found';
$pageDescription = 'The page you are looking for does not exist.';
$noIndex = true;
require_once __DIR__ . '/includes/header.php';
?>
<section class="bxm-section">
  <div class="container">
    <div class="bxm-state-box text-center" style="max-width:560px;margin:0 auto">
      <i class="bi bi-compass bxm-state-icon"></i>
      <h1 class="bxm-section-title mb-2">Page not found</h1>
      <p class="text-secondary mb-4">Something went wrong. The page you requested does not exist or may have been moved.</p>
      <div class="d-flex flex-wrap justify-content-center gap-2">
        <a href="<?= bxm_url('index.php') ?>" class="bxm-btn bxm-btn-primary">Back to Home</a>
        <a href="<?= bxm_url('products.php') ?>" class="bxm-btn bxm-btn-outline">Browse Products</a>
        <a href="<?= bxm_url('support.php') ?>" class="bxm-btn bxm-btn-outline">Contact Support</a>
      </div>
    </div>
  </div>
</section>
<?php
include __DIR__ . '/includes/footer.php';
?>
