<?php
require_once 'api_common.php';
configure_api();
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'ไม่รองรับคำขอนี้'], 405);
}

$request_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : false;
if (!$request_id) {
    json_response(['success' => false, 'message' => 'ไม่พบเอกสารแนบ'], 400);
}

try {
    require 'condb.php';
    $stmt = $conn->prepare('SELECT attachment_name, attachment_mime, attachment_data FROM leave_requests WHERE id = ? LIMIT 1');
    $stmt->execute([$request_id]);
    $attachment = $stmt->fetch(PDO::FETCH_ASSOC);

    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!$attachment || $attachment['attachment_data'] === null || !in_array($attachment['attachment_mime'], $allowed_types, true)) {
        json_response(['success' => false, 'message' => 'ไม่พบเอกสารแนบ'], 404);
    }

    $filename = basename((string) $attachment['attachment_name']);
    $filename = preg_replace('/[\r\n"]/', '_', $filename);
    header('Content-Type: ' . $attachment['attachment_mime']);
    header('Content-Disposition: inline; filename="attachment"; filename*=UTF-8\'\'' . rawurlencode($filename));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, no-store');
    echo $attachment['attachment_data'];
} catch (PDOException $error) {
    error_log('Admin attachment database error: ' . $error->getMessage());
    json_response(['success' => false, 'message' => 'เปิดเอกสารแนบไม่สำเร็จ'], 500);
}
