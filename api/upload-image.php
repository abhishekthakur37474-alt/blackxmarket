<?php
require_once __DIR__ . '/../includes/helpers.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    bxm_json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$idToken = bxm_bearer_token();
$verified = bxm_verify_id_token($idToken);

if (!$verified || empty($verified['uid'])) {
    bxm_json(['ok' => false, 'error' => 'Please login to upload images.'], 401);
}

$uid = $verified['uid'];
$record = bxm_get_user_record($uid, $idToken);
$role = $record['role'] ?? 'user';
$folder = $_POST['folder'] ?? 'misc';

$restricted = ['product', 'products', 'carousel', 'carousels'];
if (in_array($folder, $restricted, true) && $role !== 'admin') {
    bxm_json(['ok' => false, 'error' => 'Only admins can upload this image.'], 403);
}

if (empty($_FILES['image']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
    bxm_json(['ok' => false, 'error' => 'No image file received.'], 400);
}

$file = $_FILES['image'];
$imgbb = bxm_config('imgbb');

if ($file['size'] > $imgbb['maxBytes']) {
    bxm_json(['ok' => false, 'error' => 'Image is too large. Maximum size is 5MB.'], 400);
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
if (!in_array($mime, $imgbb['allowed'], true)) {
    bxm_json(['ok' => false, 'error' => 'Only JPG, PNG and WEBP images are allowed.'], 400);
}

$payload = base64_encode(file_get_contents($file['tmp_name']));
$endpoint = $imgbb['endpoint'] . '?key=' . urlencode($imgbb['key']);

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 45);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'image' => $payload,
    'name' => 'bxm-' . $folder . '-' . time(),
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($response === false) {
    bxm_log('imgbb_unavailable', ['error' => $error, 'uid' => $uid]);
    bxm_json(['ok' => false, 'error' => 'Image service unavailable. Please try again.'], 502);
}

$data = json_decode($response, true);
$url = $data['data']['url'] ?? $data['data']['display_url'] ?? null;

if ($httpCode < 200 || $httpCode >= 300 || !$url) {
    bxm_log('imgbb_upload_failed', ['status' => $httpCode, 'uid' => $uid]);
    bxm_json(['ok' => false, 'error' => 'Image upload failed. Please try again.'], 502);
}

bxm_json(['ok' => true, 'url' => $url, 'deleteUrl' => $data['data']['delete_url'] ?? '']);
