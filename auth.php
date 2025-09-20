<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple session-based captcha helpers
function generateCaptcha() {
    $a = random_int(1, 9);
    $b = random_int(1, 9);
    $_SESSION['captcha'] = [
        'q' => "$a + $b",
        'a' => (string)($a + $b),
        'ts' => time()
    ];
}

function getCaptchaQuestion() {
    if (empty($_SESSION['captcha'])) generateCaptcha();
    return $_SESSION['captcha']['q'] ?? '';
}

function verifyCaptcha($answer) {
    $ok = isset($_SESSION['captcha']['a']) && trim((string)$answer) === (string)$_SESSION['captcha']['a'];
    unset($_SESSION['captcha']);
    return $ok;
}

function setFlash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

function getFlash($key) {
    if (!isset($_SESSION['flash'][$key])) return null;
    $msg = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function currentUser() {
    return $_SESSION['user'] ?? null;
}

function requireLogin() {
    if (!currentUser()) {
        setFlash('error', 'Please login to continue.');
        header('Location: login.php');
        exit;
    }
}

function requireRole($role) {
    $user = currentUser();
    if (!$user || $user['role'] !== $role) {
        http_response_code(403);
        die('Forbidden');
    }
}

?>

