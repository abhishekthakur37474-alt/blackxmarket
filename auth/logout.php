<?php
require_once __DIR__ . '/../includes/helpers.php';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Logging out | BLACK X MARKET</title>
<link href="<?= bxm_url('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
<link href="<?= bxm_url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<div class="container bxm-center-screen">
  <div class="bxm-state-box text-center">
    <div class="bxm-spinner mx-auto mb-3"></div>
    <h5>Logging out...</h5>
  </div>
</div>
<script src="<?= bxm_url('assets/vendor/firebase/firebase-app-compat.js') ?>"></script>
<script src="<?= bxm_url('assets/vendor/firebase/firebase-auth-compat.js') ?>"></script>
<script src="<?= bxm_url('firebase/firebase-init.js') ?>"></script>
<script>
(function () {
  function go() { window.location.href = <?= json_encode(bxm_url('index.php')) ?>; }
  if (window.BXM_FIREBASE_READY && window.BXM_FB && window.BXM_FB.auth) {
    window.BXM_FB.auth.signOut().then(go).catch(go);
  } else {
    go();
  }
})();
</script>
</body>
</html>
