<?php
require_once 'api_common.php';
configure_api();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (empty($_SESSION['admin_id'])) {
        json_response(['success' => false, 'message' => 'ยังไม่ได้เข้าสู่ระบบ'], 401);
    }

    json_response([
        'success' => true,
        'admin' => [
            'id' => $_SESSION['admin_id'],
            'username' => $_SESSION['admin_username'],
        ],
    ]);
}

if ($method === 'DELETE') {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
    json_response(['success' => true]);
}

if ($method !== 'POST') {
    json_response(['success' => false, 'message' => 'ไม่รองรับคำขอนี้'], 405);
}

$data = request_data();
$login_username = isset($data['username']) ? trim($data['username']) : '';
$login_password = isset($data['password']) ? (string) $data['password'] : '';

if ($login_username === '' || $login_password === '') {
    json_response(['success' => false, 'message' => 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน'], 400);
}

try {
    require 'condb.php';
    $stmt = $conn->prepare('SELECT id, username, password FROM admin WHERE username = ? LIMIT 1');
    $stmt->execute([$login_username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    $password_matches = false;
    $legacy_password = false;
    if ($admin) {
        $password_info = password_get_info($admin['password']);
        if ($password_info['algoName'] !== 'unknown') {
            $password_matches = password_verify($login_password, $admin['password']);
        } else {
            $legacy_password = hash_equals((string) $admin['password'], $login_password);
            $password_matches = $legacy_password;
        }
    }

    if (!$password_matches) {
        json_response(['success' => false, 'message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'], 401);
    }

    if ($legacy_password) {
        $hash = password_hash($login_password, PASSWORD_DEFAULT);
        $upgrade = $conn->prepare('UPDATE admin SET password = ? WHERE id = ?');
        $upgrade->execute([$hash, $admin['id']]);
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];

    json_response([
        'success' => true,
        'admin' => [
            'id' => (int) $admin['id'],
            'username' => $admin['username'],
        ],
    ]);
} catch (PDOException $error) {
    error_log('Admin login database error: ' . $error->getMessage());
    json_response(['success' => false, 'message' => 'เชื่อมต่อฐานข้อมูลไม่สำเร็จ'], 500);
}
