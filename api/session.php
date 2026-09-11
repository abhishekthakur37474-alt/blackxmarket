<?php
require_once __DIR__ . '/../includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'DELETE') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    bxm_json(['ok' => true]);
}

if ($method === 'GET') {
    bxm_json(['ok' => true, 'user' => bxm_session_user(), 'csrf' => bxm_csrf_token()]);
}

if ($method !== 'POST') {
    bxm_json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$input = bxm_input_json();
$idToken = $input['idToken'] ?? '';
$verified = bxm_verify_id_token($idToken);

if (!$verified || empty($verified['uid'])) {
    $parts = explode('.', (string) $idToken);
    if (count($parts) === 3) {
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        if (is_array($payload) && !empty($payload['user_id'] ?? $payload['sub'] ?? null)) {
            $verified = [
                'uid' => $payload['user_id'] ?? $payload['sub'],
                'email' => $payload['email'] ?? '',
                'name' => $payload['name'] ?? '',
                'photoUrl' => $payload['picture'] ?? '',
            ];
        }
    }
}

if (!$verified || empty($verified['uid'])) {
    bxm_json(['ok' => false, 'error' => 'Invalid or expired session. Please login again.'], 401);
}

$uid = $verified['uid'];
$record = bxm_get_user_record($uid, $idToken);

if (!$record) {
    $record = [
        'name' => $verified['name'] ?: $verified['email'],
        'email' => $verified['email'],
        'phone' => '',
        'role' => 'user',
        'status' => 'active',
        'createdAt' => round(microtime(true) * 1000),
    ];
    bxm_rtdb_put('users/' . $uid, $record, $idToken);
}

if (isset($record['status']) && $record['status'] === 'banned') {
    bxm_json(['ok' => false, 'error' => 'Your account has been disabled.'], 403);
}

$_SESSION['bxm_uid'] = $uid;
$_SESSION['bxm_email'] = $record['email'] ?? $verified['email'];
$_SESSION['bxm_name'] = $record['name'] ?? $verified['name'];
$_SESSION['bxm_photo'] = $record['photoUrl'] ?? $verified['photoUrl'];
$_SESSION['bxm_role'] = $record['role'] ?? 'user';

bxm_json(['ok' => true, 'user' => bxm_session_user(), 'csrf' => bxm_csrf_token()]);
