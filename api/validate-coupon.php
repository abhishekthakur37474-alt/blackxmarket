<?php
require_once __DIR__ . '/../includes/helpers.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    bxm_json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$idToken = bxm_bearer_token();
$verified = bxm_verify_id_token($idToken);

if (!$verified || empty($verified['uid'])) {
    bxm_json(['ok' => false, 'error' => 'Please login to apply coupons.'], 401);
}

$input = bxm_input_json();
$result = bxm_validate_coupon($input['code'] ?? '', $input['cartAmount'] ?? 0, $idToken);

if (empty($result['ok'])) {
    bxm_json(['ok' => false, 'error' => $result['error'] ?? 'Invalid coupon.'], 400);
}

bxm_json([
    'ok' => true,
    'couponId' => $result['couponId'],
    'code' => $result['code'],
    'discount' => $result['discount'],
    'message' => 'Coupon applied successfully.',
]);
