<?php
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle = 'Forgot Password';
$pageDescription = 'Reset your BLACK X MARKET password.';
$noIndex = true;
require_once __DIR__ . '/../includes/header.php';
?>
<section class="bxm-auth-wrap">
  <div class="container">
    <div class="bxm-auth-card">
      <div class="text-center mb-4">
        <img src="<?= bxm_url('assets/images/logo/logo.jpeg') ?>" alt="BLACK X MARKET" width="56" height="56" class="bxm-brand-logo mb-3">
        <h1 class="h4 mb-1">Forgot Password?</h1>
        <p class="text-secondary small mb-0">Enter your email and we will send a reset link.</p>
      </div>
      <form id="forgotForm" novalidate>
        <div class="mb-4">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" id="forgotEmail" placeholder="you@example.com" required>
        </div>
        <button type="submit" class="bxm-btn bxm-btn-primary w-100" id="forgotBtn">Send Reset Link</button>
      </form>
      <p class="text-center text-secondary small mt-4 mb-0"><a href="<?= bxm_url('auth/login.php') ?>" class="text-white">Back to Login</a></p>
    </div>
  </div>
</section>
<?php
$inlineScript = <<<'HTML'
<script>
(function () {
  document.getElementById('forgotForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var email = document.getElementById('forgotEmail').value.trim();
    var btn = document.getElementById('forgotBtn');
    if (!email) { window.BXM.toast('Please enter your email', 'warning'); return; }
    if (!window.BXM.firebaseReady) { window.BXM.toast('Firebase is not configured yet', 'danger'); return; }
    btn.disabled = true;
    window.BXM.auth.sendPasswordResetEmail(email).then(function () {
      window.BXM.toast('Password reset link sent. Check your email.', 'success');
      btn.disabled = false;
    }).catch(function (err) {
      btn.disabled = false;
      window.BXM.toast(err.message || 'Could not send reset link', 'danger');
    });
  });
})();
</script>
HTML;
include __DIR__ . '/../includes/footer.php';
?>
