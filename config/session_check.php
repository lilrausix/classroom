<?php
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

initSession();

function checkRole($allowed_role) {
    initSession();

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    if ($_SESSION['role'] !== $allowed_role) {
        http_response_code(403);
        die("<h1 style='color:white; background:#0f172a; height:100vh; display:flex; align-items:center; justify-content:center; font-family:sans-serif;'>403 - Piekļuve liegta. Tev nav $allowed_role tiesību.</h1>");
    }
}

function generateCsrfToken() {
    initSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    initSession();
    return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function requireCsrf() {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Invalid CSRF token.');
    }
}

function getUploadedFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function generateRandomFilename($originalName) {
    $extension = getUploadedFileExtension($originalName);
    $extension = preg_replace('/[^a-z0-9]/', '', $extension);
    $name = bin2hex(random_bytes(16));
    return $extension !== '' ? "$name.$extension" : $name;
}

function isAllowedFileExtension($filename, array $allowedExtensions) {
    $extension = getUploadedFileExtension($filename);
    return in_array($extension, $allowedExtensions, true);
}

function saveUploadedFile(array $file, string $targetDir, array $allowedExtensions) {
    if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return false;
    }

    if (!isAllowedFileExtension($file['name'], $allowedExtensions)) {
        return false;
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $savedName = generateRandomFilename($file['name']);
    $destination = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $savedName;

    return move_uploaded_file($file['tmp_name'], $destination) ? $savedName : false;
}
?>