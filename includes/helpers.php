<?php

require_once __DIR__ . '/config.php';

function bxm_config($key = null)
{
    $cfg = $GLOBALS['BXM'];
    if ($key === null) {
        return $cfg;
    }
    return isset($cfg[$key]) ? $cfg[$key] : null;
}

function bxm_site($key = null)
{
    $site = bxm_config('site');
    return $key === null ? $site : (isset($site[$key]) ? $site[$key] : null);
}

function bxm_firebase($key = null)
{
    $fb = bxm_config('firebase');
    return $key === null ? $fb : (isset($fb[$key]) ? $fb[$key] : null);
}

function bxm_e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function bxm_url($path = '')
{
    return BXM_BASE . '/' . ltrim($path, '/');
}

function bxm_money($amount)
{
    return bxm_site('currency') . number_format((float) $amount, 2);
}

function bxm_json($data, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function bxm_input_json()
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function bxm_bearer_token()
{
    $header = '';
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $header = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        foreach ($headers as $name => $value) {
            if (strtolower($name) === 'authorization') {
                $header = $value;
                break;
            }
        }
    }
    if (stripos($header, 'Bearer ') === 0) {
        return trim(substr($header, 7));
    }
    return '';
}

function bxm_rtdb_url($path, $token = null)
{
    $base = rtrim(bxm_firebase('databaseURL'), '/');
    $url = $base . '/' . ltrim($path, '/') . '.json';
    if ($token) {
        $url .= '?auth=' . urlencode($token);
    }
    return $url;
}

function bxm_rtdb_request($method, $path, $token = null, $body = null)
{
    $url = bxm_rtdb_url($path, $token);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['ok' => false, 'error' => $error, 'status' => 0, 'data' => null];
    }
    return [
        'ok' => $httpCode >= 200 && $httpCode < 300,
        'error' => $httpCode >= 400 ? $response : null,
        'status' => $httpCode,
        'data' => json_decode($response, true),
    ];
}

function bxm_rtdb_get($path, $token = null)
{
    return bxm_rtdb_request('GET', $path, $token);
}

function bxm_rtdb_put($path, $data, $token = null)
{
    return bxm_rtdb_request('PUT', $path, $token, $data);
}

function bxm_rtdb_patch($path, $data, $token = null)
{
    return bxm_rtdb_request('PATCH', $path, $token, $data);
}

function bxm_rtdb_post($path, $data, $token = null)
{
    return bxm_rtdb_request('POST', $path, $token, $data);
}

function bxm_verify_id_token($idToken)
{
    if (!$idToken) {
        return null;
    }
    $apiKey = bxm_firebase('apiKey');
    $url = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . urlencode($apiKey);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['idToken' => $idToken]));
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        return null;
    }
    $data = json_decode($response, true);
    if (empty($data['users'][0])) {
        return null;
    }
    $user = $data['users'][0];
    return [
        'uid' => $user['localId'] ?? null,
        'email' => $user['email'] ?? '',
        'name' => $user['displayName'] ?? '',
        'photoUrl' => $user['photoUrl'] ?? '',
    ];
}

function bxm_get_user_record($uid, $idToken = null)
{
    if (!$uid) {
        return null;
    }
    $result = bxm_rtdb_get('users/' . $uid, $idToken);
    if (!empty($result['ok']) && is_array($result['data'])) {
        return $result['data'];
    }
    return null;
}

function bxm_session_user()
{
    if (empty($_SESSION['bxm_uid'])) {
        return null;
    }
    return [
        'uid' => $_SESSION['bxm_uid'],
        'email' => $_SESSION['bxm_email'] ?? '',
        'name' => $_SESSION['bxm_name'] ?? '',
        'photoUrl' => $_SESSION['bxm_photo'] ?? '',
        'role' => $_SESSION['bxm_role'] ?? 'user',
    ];
}

function bxm_is_logged_in()
{
    return bxm_session_user() !== null;
}

function bxm_is_admin()
{
    $user = bxm_session_user();
    return $user !== null && $user['role'] === 'admin';
}

function bxm_has_admin()
{
    $result = bxm_rtdb_get('users');
    if (empty($result['ok']) || !is_array($result['data'])) {
        return false;
    }
    foreach ($result['data'] as $user) {
        if (is_array($user) && (($user['role'] ?? '') === 'admin')) {
            return true;
        }
    }
    return false;
}

function bxm_redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function bxm_discount_percent($original, $discounted)
{
    $original = (float) $original;
    $discounted = (float) $discounted;
    if ($original <= 0) {
        return 0;
    }
    $percent = (($original - $discounted) / $original) * 100;
    return round(max(0, $percent), 2);
}

function bxm_product_status_label($status)
{
    $map = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'out_of_stock' => 'Out of Stock',
    ];
    return isset($map[$status]) ? $map[$status] : ucfirst((string) $status);
}

function bxm_order_status_label($status)
{
    $map = [
        'pending' => 'Pending',
        'payment_submitted' => 'Payment Submitted',
        'under_review' => 'Under Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];
    return isset($map[$status]) ? $map[$status] : ucfirst((string) $status);
}

function bxm_validate_coupon($code, $cartAmount, $idToken)
{
    $code = strtoupper(trim((string) $code));
    $cartAmount = (float) $cartAmount;

    if ($code === '') {
        return ['ok' => false, 'error' => 'Please enter a coupon code.'];
    }

    $result = bxm_rtdb_get('coupons', $idToken);
    if (empty($result['ok']) || !is_array($result['data'])) {
        return ['ok' => false, 'error' => 'Coupons are unavailable right now.'];
    }

    $coupon = null;
    foreach ($result['data'] as $couponId => $item) {
        if (isset($item['code']) && strtoupper(trim($item['code'])) === $code) {
            $coupon = $item;
            $coupon['_id'] = $couponId;
            break;
        }
    }

    if (!$coupon) {
        return ['ok' => false, 'error' => 'Invalid coupon code.'];
    }
    if (isset($coupon['status']) && $coupon['status'] !== 'active') {
        return ['ok' => false, 'error' => 'This coupon is no longer active.'];
    }

    if (!empty($coupon['expiryDate'])) {
        $expiry = $coupon['expiryDate'];
        if (is_numeric($expiry)) {
            $expiryTs = (float) $expiry;
            if ($expiryTs > 100000000000) {
                $expiryTs = $expiryTs / 1000;
            }
        } else {
            $expiryTs = strtotime($expiry);
        }
        if ($expiryTs && time() > $expiryTs + 86399) {
            return ['ok' => false, 'error' => 'This coupon has expired.'];
        }
    }

    $minAmount = (float) ($coupon['minAmount'] ?? 0);
    if ($cartAmount < $minAmount) {
        return ['ok' => false, 'error' => 'Minimum cart amount for this coupon is ' . bxm_money($minAmount) . '.'];
    }

    $usageLimit = (int) ($coupon['usageLimit'] ?? 0);
    $usedCount = (int) ($coupon['usedCount'] ?? 0);
    if ($usageLimit > 0 && $usedCount >= $usageLimit) {
        return ['ok' => false, 'error' => 'This coupon usage limit has been reached.'];
    }

    $type = $coupon['type'] ?? 'percentage';
    $value = (float) ($coupon['value'] ?? 0);
    $discount = $type === 'flat' ? $value : $cartAmount * ($value / 100);

    $maxDiscount = (float) ($coupon['maxDiscount'] ?? 0);
    if ($maxDiscount > 0 && $discount > $maxDiscount) {
        $discount = $maxDiscount;
    }
    $discount = min($discount, $cartAmount);

    return [
        'ok' => true,
        'couponId' => $coupon['_id'],
        'code' => strtoupper($coupon['code']),
        'discount' => round($discount, 2),
    ];
}

function bxm_generate_order_id($idToken)
{
    for ($i = 0; $i < 6; $i++) {
        $candidate = 'BXM-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $check = bxm_rtdb_get('orders/' . $candidate, $idToken);
        if (empty($check['data'])) {
            return $candidate;
        }
    }
    return 'BXM-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(5)));
}

function bxm_csrf_token()
{
    if (empty($_SESSION['bxm_csrf'])) {
        $_SESSION['bxm_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['bxm_csrf'];
}

function bxm_csrf_header()
{
    if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        return (string) $_SERVER['HTTP_X_CSRF_TOKEN'];
    }
    return '';
}

function bxm_csrf_verify()
{
    $sent = bxm_csrf_header();
    if ($sent === '') {
        return false;
    }
    return hash_equals(bxm_csrf_token(), $sent);
}

function bxm_require_csrf()
{
    if (!bxm_csrf_verify()) {
        bxm_json(['ok' => false, 'error' => 'Invalid request token. Please refresh the page and try again.'], 403);
    }
}

function bxm_log($message, $context = [])
{
    $line = '[BXM] ' . $message;
    if (!empty($context)) {
        $line .= ' ' . json_encode($context);
    }
    error_log($line);
}
