<?php
require_once __DIR__ . '/admin-check.php';

$adminTitle = isset($adminTitle) ? $adminTitle : 'Dashboard';
$adminActive = isset($adminActive) ? $adminActive : 'dashboard';
$siteName = bxm_site('name');

$adminNav = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => bxm_url('admin/index.php'), 'icon' => 'bi-speedometer2'],
    ['group' => 'Management'],
    ['key' => 'users', 'label' => 'Users', 'href' => bxm_url('admin/users.php'), 'icon' => 'bi-people'],
    ['key' => 'products', 'label' => 'Products', 'href' => bxm_url('admin/products.php'), 'icon' => 'bi-box-seam'],
    ['key' => 'orders', 'label' => 'Orders', 'href' => bxm_url('admin/orders.php'), 'icon' => 'bi-receipt'],
    ['key' => 'payments', 'label' => 'Payments', 'href' => bxm_url('admin/payments.php'), 'icon' => 'bi-credit-card'],
    ['key' => 'carousels', 'label' => 'Carousels', 'href' => bxm_url('admin/carousels.php'), 'icon' => 'bi-images'],
    ['key' => 'coupons', 'label' => 'Coupons', 'href' => bxm_url('admin/coupons.php'), 'icon' => 'bi-ticket-perforated'],
    ['key' => 'reviews', 'label' => 'Reviews', 'href' => bxm_url('admin/reviews.php'), 'icon' => 'bi-star'],
    ['key' => 'support', 'label' => 'Support', 'href' => bxm_url('admin/support.php'), 'icon' => 'bi-life-preserver'],
    ['group' => 'Settings'],
    ['key' => 'settings', 'label' => 'Settings', 'href' => bxm_url('admin/settings.php'), 'icon' => 'bi-gear'],
];

function bxm_admin_nav_render($adminNav, $adminActive)
{
    foreach ($adminNav as $item) {
        if (isset($item['group'])) {
            echo '<div class="bxm-admin-nav-label">' . bxm_e($item['group']) . '</div>';
            continue;
        }
        $active = $item['key'] === $adminActive ? ' active' : '';
        echo '<a href="' . bxm_e($item['href']) . '" class="' . trim($active) . '"><i class="bi ' . bxm_e($item['icon']) . '"></i> ' . bxm_e($item['label']) . '</a>';
    }
}
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= bxm_e($adminTitle) ?> | Admin - <?= bxm_e($siteName) ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="<?= bxm_url('assets/images/logo/logo.jpeg') ?>">
<link href="<?= bxm_url('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
<link href="<?= bxm_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
<link href="<?= bxm_url('assets/css/style.css') ?>" rel="stylesheet">
<link href="<?= bxm_url('assets/css/admin.css') ?>" rel="stylesheet">
<script>
window.BXM_FIREBASE_CONFIG = <?= json_encode(bxm_firebase()) ?>;
window.BXM_APP = { base: <?= json_encode(BXM_BASE) ?>, currency: <?= json_encode(bxm_site('currency')) ?>, csrf: <?= json_encode(bxm_csrf_token()) ?>, firebaseReady: false, role: <?= json_encode($currentUser['role'] ?? '') ?> };
</script>
</head>
<body class="bxm-admin-body">
<div class="bxm-toast-stack" id="bxmToastStack"></div>
<div class="bxm-admin-shell">
  <aside class="bxm-admin-sidebar">
    <a href="<?= bxm_url('admin/index.php') ?>" class="bxm-admin-logo">
      <img src="<?= bxm_url('assets/images/logo/logo.jpeg') ?>" alt="BLACK X MARKET" width="38" height="38">
      <div><div class="title">BLACK X MARKET</div><div class="sub">Admin Panel</div></div>
    </a>
    <nav class="bxm-admin-nav"><?php bxm_admin_nav_render($adminNav, $adminActive); ?></nav>
    <div class="bxm-admin-nav-label">Account</div>
    <nav class="bxm-admin-nav">
      <a href="<?= bxm_url('index.php') ?>"><i class="bi bi-box-arrow-up-right"></i> View Site</a>
      <a href="#" data-bxm-logout><i class="bi bi-box-arrow-right"></i> Logout</a>
    </nav>
  </aside>

  <div class="bxm-admin-main">
    <div class="bxm-admin-topbar">
      <button class="bxm-icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminOffcanvas" aria-label="Menu"><i class="bi bi-list"></i></button>
      <div class="flex-grow-1">
        <div class="fw-semibold"><?= bxm_e($adminTitle) ?></div>
      </div>
      <span class="bxm-badge d-none d-sm-inline-flex"><i class="bi bi-shield-lock"></i> <?= bxm_e($currentUser['name'] ?: 'Admin') ?></span>
    </div>
    <div class="bxm-admin-content">
      <div id="bxmConfigNotice"></div>
