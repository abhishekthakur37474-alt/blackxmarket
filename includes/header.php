<?php
require_once __DIR__ . '/helpers.php';

$pageTitle = isset($pageTitle) ? $pageTitle : 'Home';
$pageDescription = isset($pageDescription) ? $pageDescription : 'BLACK X MARKET - premium digital gaming products delivered instantly.';
$activePage = isset($activePage) ? $activePage : '';
$noIndex = isset($noIndex) ? $noIndex : false;
$bodyClass = isset($bodyClass) ? $bodyClass : '';
$siteName = bxm_site('name');
$canonical = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= bxm_e($pageTitle) ?> | <?= bxm_e($siteName) ?></title>
<meta name="description" content="<?= bxm_e($pageDescription) ?>">
<?php if ($noIndex): ?>
<meta name="robots" content="noindex, nofollow">
<?php endif; ?>
<link rel="canonical" href="<?= bxm_e($canonical) ?>">
<meta property="og:title" content="<?= bxm_e($pageTitle) ?> | <?= bxm_e($siteName) ?>">
<meta property="og:description" content="<?= bxm_e($pageDescription) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= bxm_e($siteName) ?>">
<meta property="og:url" content="<?= bxm_e($canonical) ?>">
<meta property="og:image" content="<?= bxm_e((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . bxm_url('assets/images/logo/logo.jpeg')) ?>">
<meta name="twitter:card" content="summary">
<link rel="icon" href="<?= bxm_url('assets/images/logo/logo.jpeg') ?>">
<link href="<?= bxm_url('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
<link href="<?= bxm_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
<link href="<?= bxm_url('assets/css/style.css') ?>" rel="stylesheet">
<script>
window.BXM_FIREBASE_CONFIG = <?= json_encode(bxm_firebase()) ?>;
window.BXM_APP = {
  base: <?= json_encode(BXM_BASE) ?>,
  currency: <?= json_encode(bxm_site('currency')) ?>,
  csrf: <?= json_encode(bxm_csrf_token()) ?>,
  firebaseReady: false
};
</script>
</head>
<body class="<?= bxm_e($bodyClass) ?>">
<a class="bxm-skip-link" href="#bxm-main">Skip to content</a>
<div class="bxm-toast-stack" id="bxmToastStack"></div>
<?php include __DIR__ . '/navbar.php'; ?>
<main id="bxm-main" tabindex="-1">
