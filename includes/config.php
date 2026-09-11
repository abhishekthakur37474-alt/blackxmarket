<?php

if (!defined('BXM_DEBUG')) {
    define('BXM_DEBUG', false);
}

if (!BXM_DEBUG) {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
}

$BXM = [
    'firebase' => [
        'apiKey' => 'AIzaSyCEgnp4nzOoVg1zu3ywRLHjQY3uM0dA8Eo',
        'authDomain' => 'blackxmarket-e227b.firebaseapp.com',
        'databaseURL' => 'https://blackxmarket-e227b-default-rtdb.asia-southeast1.firebasedatabase.app',
        'projectId' => 'blackxmarket-e227b',
        'storageBucket' => 'blackxmarket-e227b.firebasestorage.app',
        'messagingSenderId' => '1002862140509',
        'appId' => '1:1002862140509:web:813d12df2a0183625641b4',
        'measurementId' => 'G-3JQJNQYBGF',
    ],
    'imgbb' => [
        'endpoint' => 'https://api.imgbb.com/1/upload',
        'key' => 'ce94b9f4039dd440aafebf96f1282e39',
        'maxBytes' => 5242880,
        'allowed' => ['image/jpeg', 'image/png', 'image/webp'],
    ],
    'site' => [
        'name' => 'BLACK X MARKET',
        'tagline' => 'DIGITAL GAMING STUFF, DELIVERED.',
        'currency' => '₹',
        'year' => date('Y'),
    ],
    'base' => '',
];

$GLOBALS['BXM'] = $BXM;

function bxm_detect_base()
{
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])) : '';
    $projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..'));

    if ($docRoot && $projectRoot && strpos($projectRoot, $docRoot) === 0) {
        $base = substr($projectRoot, strlen($docRoot));
        $base = str_replace('\\', '/', $base);
        return rtrim($base, '/') ?: '';
    }

    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $scriptDir = str_replace('\\', '/', dirname($script));
    $leaf = basename($scriptDir);
    if (in_array($leaf, ['auth', 'admin', 'api', 'firebase', 'includes', 'assets'], true)) {
        $scriptDir = str_replace('\\', '/', dirname($scriptDir));
    }
    if ($scriptDir === '/' || $scriptDir === '.' || $scriptDir === '\\') {
        return '';
    }
    return rtrim($scriptDir, '/');
}

if (!defined('BXM_BASE')) {
    $detectedBase = $BXM['base'] !== '' ? $BXM['base'] : bxm_detect_base();
    define('BXM_BASE', $detectedBase);
    $BXM['base'] = $detectedBase;
    $GLOBALS['BXM'] = $BXM;
}

if (session_status() === PHP_SESSION_NONE) {
    $cookiePath = BXM_BASE !== '' ? BXM_BASE : '/';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $cookiePath,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
