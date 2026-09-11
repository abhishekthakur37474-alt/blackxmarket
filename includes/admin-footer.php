<?php
$inlineScript = isset($inlineScript) ? $inlineScript : '';
?>
    </div>
  </div>
</div>

<div class="offcanvas offcanvas-start bxm-admin-offcanvas" tabindex="-1" id="adminOffcanvas">
  <div class="offcanvas-header">
    <span class="fw-bold">ADMIN PANEL</span>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <nav class="bxm-admin-nav"><?php bxm_admin_nav_render($adminNav, $adminActive); ?></nav>
    <div class="bxm-admin-nav-label">Account</div>
    <nav class="bxm-admin-nav">
      <a href="<?= bxm_url('index.php') ?>"><i class="bi bi-box-arrow-up-right"></i> View Site</a>
      <a href="#" data-bxm-logout><i class="bi bi-box-arrow-right"></i> Logout</a>
    </nav>
  </div>
</div>

<div class="bxm-loader" id="bxmLoader"><div class="bxm-spinner"></div></div>

<script src="<?= bxm_url('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= bxm_url('assets/vendor/firebase/firebase-app-compat.js') ?>"></script>
<script src="<?= bxm_url('assets/vendor/firebase/firebase-auth-compat.js') ?>"></script>
<script src="<?= bxm_url('assets/vendor/firebase/firebase-database-compat.js') ?>"></script>
<script src="<?= bxm_url('firebase/firebase-init.js') ?>"></script>
<script src="<?= bxm_url('assets/js/app.js') ?>"></script>
<script src="<?= bxm_url('assets/js/admin.js') ?>"></script>
<?= $inlineScript ?>
</body>
</html>
