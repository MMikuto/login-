<?php
function configure_api()
{
    $allowed_origins = [
        'http://localhost',
        'http://localhost:8080',
        'http://127.0.0.1:8080',
    ];
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

    if ($origin !== '' && in_array($origin, $allowed_origins, true)) {
        header("Access-Control-Allow-Origin: {$origin}");
        header('Access-Control-Allow-Credentials: true');
        header('Vary: Origin');
    }

    header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params([
            'httponly' => true,
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Lax',
            'path' => '/',
        ]);
        session_start();
    }
}

function json_response($data, $status = 200)
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function request_data()
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!is_array($data)) {
        json_response(['success' => false, 'message' => 'ข้อมูลคำขอไม่ถูกต้อง'], 400);
    }

    return $data;
}

function require_role($role)
{
    if (empty($_SESSION['account_role'])) {
        json_response(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ'], 401);
    }

    if ($_SESSION['account_role'] !== $role) {
        json_response(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการนี้'], 403);
    }
}

function require_admin()
{
    require_role('admin');
}
