<?php
require_once __DIR__ . '/helpers.php';

if (!bxm_is_logged_in()) {
    $redirect = $_SERVER['REQUEST_URI'] ?? bxm_url('index.php');
    bxm_redirect(bxm_url('auth/login.php?redirect=' . urlencode($redirect)));
}

$currentUser = bxm_session_user();
