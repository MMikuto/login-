<?php
require_once 'api_common.php';
configure_api();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (empty($_SESSION['account_role'])) {
        json_response(['success' => false, 'message' => 'ยังไม่ได้เข้าสู่ระบบ'], 401);
    }

    json_response([
        'success' => true,
        'role' => $_SESSION['account_role'],
        'account' => [
            'id' => $_SESSION['account_id'],
            'username' => $_SESSION['account_username'],
            'fullName' => isset($_SESSION['account_full_name']) ? $_SESSION['account_full_name'] : null,
            'department' => isset($_SESSION['account_department']) ? $_SESSION['account_department'] : null,
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
$role = isset($data['role']) ? $data['role'] : 'user';
$login_username = isset($data['username']) ? trim($data['username']) : '';
$login_password = isset($data['password']) ? (string) $data['password'] : '';

if (!in_array($role, ['user', 'admin'], true)) {
    json_response(['success' => false, 'message' => 'ประเภทบัญชีไม่ถูกต้อง'], 400);
}

if ($login_username === '' || $login_password === '') {
    json_response(['success' => false, 'message' => 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน'], 400);
}

try {
    require 'condb.php';
    $table = $role === 'admin' ? 'admin' : 'users';
    $columns = $role === 'admin'
        ? 'id, username, password'
        : 'id, username, password, full_name, department';
    $stmt = $conn->prepare("SELECT {$columns} FROM {$table} WHERE username = ? LIMIT 1");
    $stmt->execute([$login_username]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    $password_matches = false;
    $legacy_password = false;
    if ($account) {
        $password_info = password_get_info($account['password']);
        if ($password_info['algoName'] !== 'unknown') {
            $password_matches = password_verify($login_password, $account['password']);
        } else {
            $legacy_password = hash_equals((string) $account['password'], $login_password);
            $password_matches = $legacy_password;
        }
    }

    if (!$password_matches) {
        json_response(['success' => false, 'message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'], 401);
    }

    if ($legacy_password) {
        $hash = password_hash($login_password, PASSWORD_DEFAULT);
        $upgrade = $conn->prepare("UPDATE {$table} SET password = ? WHERE id = ?");
        $upgrade->execute([$hash, $account['id']]);
    }

    session_regenerate_id(true);
    $_SESSION['account_id'] = (int) $account['id'];
    $_SESSION['account_role'] = $role;
    $_SESSION['account_username'] = $account['username'];
    $_SESSION['account_full_name'] = isset($account['full_name']) ? $account['full_name'] : null;
    $_SESSION['account_department'] = isset($account['department']) ? $account['department'] : null;

    json_response([
        'success' => true,
        'role' => $role,
        'account' => [
            'id' => (int) $account['id'],
            'username' => $account['username'],
            'fullName' => isset($account['full_name']) ? $account['full_name'] : null,
            'department' => isset($account['department']) ? $account['department'] : null,
        ],
    ]);
} catch (PDOException $error) {
    error_log('Login database error: ' . $error->getMessage());
    json_response(['success' => false, 'message' => 'เชื่อมต่อฐานข้อมูลไม่สำเร็จ'], 500);
}
