<?php
require_once __DIR__ . '/helpers.php';

if (!bxm_is_logged_in() || !bxm_is_admin()) {
    bxm_redirect(bxm_url('admin/login.php'));
}

$currentUser = bxm_session_user();
