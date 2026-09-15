<?php
require_once __DIR__ . '/../includes/helpers.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    bxm_json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$idToken = bxm_bearer_token();
$verified = bxm_verify_id_token($idToken);

if (!$verified || empty($verified['uid'])) {
    bxm_json(['ok' => false, 'error' => 'Please login to place an order.'], 401);
}

$uid = $verified['uid'];
$record = bxm_get_user_record($uid, $idToken);
if (isset($record['status']) && $record['status'] === 'banned') {
    bxm_json(['ok' => false, 'error' => 'Your account has been disabled.'], 403);
}

$input = bxm_input_json();
$transactionId = trim($input['transactionId'] ?? '');
$paymentScreenshotUrl = trim($input['paymentScreenshotUrl'] ?? '');
$couponCode = trim($input['couponCode'] ?? '');

if ($transactionId === '') {
    bxm_json(['ok' => false, 'error' => 'Transaction ID is required.'], 400);
}
if ($paymentScreenshotUrl === '') {
    bxm_json(['ok' => false, 'error' => 'Payment screenshot is required.'], 400);
}

$cartResult = bxm_rtdb_get('carts/' . $uid, $idToken);
$cart = (!empty($cartResult['ok']) && is_array($cartResult['data'])) ? $cartResult['data'] : [];

if (empty($cart)) {
    bxm_json(['ok' => false, 'error' => 'No product selected.'], 400);
}

$productsResult = bxm_rtdb_get('products', $idToken);
$products = (!empty($productsResult['ok']) && is_array($productsResult['data'])) ? $productsResult['data'] : [];

$items = [];
$subtotal = 0;

foreach ($cart as $productId => $entry) {
    if (!isset($products[$productId])) {
        continue;
    }
    $product = $products[$productId];
    $status = $product['status'] ?? 'active';
    if ($status !== 'active') {
        bxm_json(['ok' => false, 'error' => 'This product is no longer available.'], 400);
    }
    $quantity = max(1, (int) ($entry['quantity'] ?? 1));
    $price = (float) ($product['discountedPrice'] ?? $product['originalPrice'] ?? 0);
    $lineTotal = round($price * $quantity, 2);
    $subtotal += $lineTotal;
    $items[] = [
        'productId' => $productId,
        'title' => $product['title'] ?? 'Product',
        'category' => $product['category'] ?? '',
        'thumbnailUrl' => $product['thumbnailUrl'] ?? '',
        'price' => $price,
        'quantity' => $quantity,
        'lineTotal' => $lineTotal,
    ];
}

if (empty($items)) {
    bxm_json(['ok' => false, 'error' => 'This product is no longer available.'], 400);
}

$subtotal = round($subtotal, 2);
$couponDiscount = 0;
$couponId = '';
$appliedCode = '';

if ($couponCode !== '') {
    $coupon = bxm_validate_coupon($couponCode, $subtotal, $idToken);
    if (empty($coupon['ok'])) {
        bxm_json(['ok' => false, 'error' => $coupon['error'] ?? 'Invalid coupon.'], 400);
    }
    $couponDiscount = $coupon['discount'];
    $couponId = $coupon['couponId'];
    $appliedCode = $coupon['code'];
}

$amount = round(max(0, $subtotal - $couponDiscount), 2);

$settingsResult = bxm_rtdb_get('settings/payment', $idToken);
$paymentSettings = (!empty($settingsResult['ok']) && is_array($settingsResult['data'])) ? $settingsResult['data'] : [];

$orderId = bxm_generate_order_id($idToken);
$now = round(microtime(true) * 1000);

$order = [
    'orderId' => $orderId,
    'uid' => $uid,
    'userEmail' => $verified['email'],
    'items' => $items,
    'productId' => $items[0]['productId'],
    'productTitle' => count($items) > 1 ? ($items[0]['title'] . ' +' . (count($items) - 1) . ' more') : $items[0]['title'],
    'productThumbnail' => $items[0]['thumbnailUrl'],
    'subtotal' => $subtotal,
    'couponCode' => $appliedCode,
    'couponId' => $couponId,
    'couponDiscount' => $couponDiscount,
    'amount' => $amount,
    'transactionId' => $transactionId,
    'paymentScreenshotUrl' => $paymentScreenshotUrl,
    'paymentSnapshot' => [
        'upiId' => $paymentSettings['upiId'] ?? '',
        'qrCodeUrl' => $paymentSettings['qrCodeUrl'] ?? '',
        'paymentDisplayName' => $paymentSettings['paymentDisplayName'] ?? '',
    ],
    'status' => 'payment_submitted',
    'rejectionReason' => '',
    'createdAt' => $now,
    'updatedAt' => $now,
];

$write = bxm_rtdb_put('orders/' . $orderId, $order, $idToken);
if (empty($write['ok'])) {
    bxm_log('order_create_failed', ['orderId' => $orderId, 'uid' => $uid, 'status' => $write['status'] ?? 0]);
    bxm_json(['ok' => false, 'error' => 'Could not create your order. Please try again.'], 502);
}

if ($couponId !== '') {
    $couponRow = bxm_rtdb_get('coupons/' . $couponId, $idToken);
    $usedCount = 0;
    if (!empty($couponRow['ok']) && is_array($couponRow['data'])) {
        $usedCount = (int) ($couponRow['data']['usedCount'] ?? 0);
    }
    $inc = bxm_rtdb_put('coupons/' . $couponId . '/usedCount', $usedCount + 1, $idToken);
    if (empty($inc['ok'])) {
        bxm_log('coupon_increment_failed', ['couponId' => $couponId, 'orderId' => $orderId]);
    }
}

bxm_rtdb_put('users/' . $uid . '/orders/' . $orderId, true, $idToken);
bxm_rtdb_put('carts/' . $uid, null, $idToken);

bxm_rtdb_post('notifications/' . $uid, [
    'title' => 'Payment submitted',
    'message' => 'We received your payment for ' . $order['productTitle'] . '. Your order is now pending verification.',
    'type' => 'info',
    'link' => bxm_url('order-details.php?id=' . $orderId),
    'read' => false,
    'createdAt' => $now,
], $idToken);

bxm_json([
    'ok' => true,
    'orderId' => $orderId,
    'amount' => $amount,
    'subtotal' => $subtotal,
    'couponDiscount' => $couponDiscount,
]);
