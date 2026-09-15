<?php
$navItems = [
    'home' => ['label' => 'Home', 'href' => bxm_url('index.php')],
    'products' => ['label' => 'Products', 'href' => bxm_url('products.php')],
    'about' => ['label' => 'About', 'href' => bxm_url('about.php')],
    'reviews' => ['label' => 'Reviews', 'href' => bxm_url('reviews.php')],
    'support' => ['label' => 'Support', 'href' => bxm_url('support.php')],
];
?>
<header class="bxm-header sticky-top">
  <nav class="navbar navbar-expand-lg bxm-navbar">
    <div class="container">
      <a class="navbar-brand bxm-brand" href="<?= bxm_url('index.php') ?>">
        <img src="<?= bxm_url('assets/images/logo/logo.jpeg') ?>" alt="BLACK X MARKET" width="36" height="36" class="bxm-brand-logo">
        <span>BLACK<span class="bxm-brand-accent">X</span>MARKET</span>
      </a>

      <div class="d-flex align-items-center order-lg-3 gap-1">
        <button class="btn bxm-icon-btn d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#bxmNav" aria-controls="bxmNav" aria-expanded="false" aria-label="Menu">
          <i class="bi bi-list"></i>
        </button>
        <a class="btn bxm-icon-btn position-relative" href="<?= bxm_url('wishlist.php') ?>" aria-label="Wishlist">
          <i class="bi bi-heart"></i>
          <span class="badge bxm-count-badge" data-bxm-wishlist-count hidden>0</span>
        </a>
        <div class="dropdown bxm-nav-dropdown" data-bxm-user-menu hidden>
          <button class="btn bxm-icon-btn position-relative dropdown-toggle bxm-notif-toggle" type="button" data-bs-toggle="dropdown" data-bs-display="static" data-bs-auto-close="outside" aria-label="Notifications">
            <i class="bi bi-bell"></i>
            <span class="badge bxm-count-badge" data-bxm-notif-count hidden>0</span>
          </button>
          <div class="dropdown-menu dropdown-menu-end bxm-dropdown bxm-notif-menu">
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom bxm-divider">
              <span class="fw-semibold small">Notifications</span>
              <button type="button" class="bxm-copy-btn" data-bxm-notif-all>Mark all read</button>
            </div>
            <div class="bxm-notif-list" data-bxm-notif-list></div>
            <a class="dropdown-item text-center small border-top bxm-divider" href="<?= bxm_url('notifications.php') ?>">View all notifications</a>
          </div>
        </div>
        <div class="dropdown bxm-nav-dropdown" data-bxm-user-menu hidden>
          <button class="btn bxm-icon-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-label="Account">
            <i class="bi bi-person"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end bxm-dropdown">
            <li><span class="dropdown-item-text small text-secondary" data-bxm-user-name>Account</span></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= bxm_url('profile.php') ?>"><i class="bi bi-person-circle me-2"></i>My Profile</a></li>
            <li><a class="dropdown-item" href="<?= bxm_url('orders.php') ?>"><i class="bi bi-receipt me-2"></i>My Orders</a></li>
            <li><a class="dropdown-item" href="<?= bxm_url('notifications.php') ?>"><i class="bi bi-bell me-2"></i>Notifications</a></li>
            <li><a class="dropdown-item" href="<?= bxm_url('wishlist.php') ?>"><i class="bi bi-heart me-2"></i>Wishlist</a></li>
            <li><a class="dropdown-item" href="<?= bxm_url('admin/index.php') ?>" data-bxm-admin-link hidden><i class="bi bi-shield-lock me-2"></i>Admin Panel</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="#" data-bxm-logout><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
          </ul>
        </div>
        <a class="btn bxm-btn bxm-btn-outline bxm-auth-nav-btn" href="<?= bxm_url('auth/login.php') ?>" data-bxm-guest hidden>Login</a>
        <a class="btn bxm-btn bxm-btn-primary bxm-auth-nav-btn" href="<?= bxm_url('auth/register.php') ?>" data-bxm-guest hidden>Register</a>
      </div>

      <div class="collapse navbar-collapse order-lg-2" id="bxmNav">
        <form class="bxm-search d-flex mx-lg-auto my-3 my-lg-0" role="search" action="<?= bxm_url('products.php') ?>" method="get">
          <i class="bi bi-search"></i>
          <input class="form-control" type="search" name="q" placeholder="Search digital products..." aria-label="Search" data-bxm-search>
        </form>
        <ul class="navbar-nav bxm-nav-links">
          <?php foreach ($navItems as $key => $item): ?>
          <li class="nav-item">
            <a class="nav-link<?= $activePage === $key ? ' active' : '' ?>" href="<?= bxm_e($item['href']) ?>"<?= $activePage === $key ? ' aria-current="page"' : '' ?>><?= bxm_e($item['label']) ?></a>
          </li>
          <?php endforeach; ?>
          <li class="nav-item d-lg-none" data-bxm-guest hidden>
            <a class="nav-link" href="<?= bxm_url('auth/login.php') ?>">Login</a>
          </li>
          <li class="nav-item d-lg-none" data-bxm-guest hidden>
            <a class="nav-link" href="<?= bxm_url('auth/register.php') ?>">Register</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</header>
