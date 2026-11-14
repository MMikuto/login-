<?php
require_once 'api_common.php';
configure_api();
require_admin();

$method = $_SERVER['REQUEST_METHOD'];

try {
    require 'condb.php';

    if ($method === 'GET') {
        $stmt = $conn->query(
            "SELECT lr.id, lr.employee_name, lr.department, lr.leave_type, lr.start_date, lr.end_date, "
            . "lr.total_days, lr.reason, lr.attachment_name, lr.status, lr.created_at, u.username "
            . "FROM leave_requests lr LEFT JOIN users u ON u.id = lr.user_id "
            . "ORDER BY (lr.status = 'pending') DESC, lr.created_at DESC, lr.id DESC"
        );
        json_response(['success' => true, 'requests' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($method === 'PATCH') {
        $data = request_data();
        $request_id = isset($data['id']) ? filter_var($data['id'], FILTER_VALIDATE_INT) : false;
        $status = isset($data['status']) ? $data['status'] : '';
        if (!$request_id || !in_array($status, ['approved', 'rejected'], true)) {
            json_response(['success' => false, 'message' => 'ข้อมูลการพิจารณาไม่ถูกต้อง'], 400);
        }

        $stmt = $conn->prepare(
            "UPDATE leave_requests SET status = ?, reviewed_by = ?, reviewed_at = CURRENT_TIMESTAMP "
            . "WHERE id = ? AND status = 'pending'"
        );
        $stmt->execute([$status, (int) $_SESSION['account_id'], $request_id]);
        if ($stmt->rowCount() !== 1) {
            json_response(['success' => false, 'message' => 'คำขอนี้ถูกพิจารณาแล้วหรือไม่พบคำขอ'], 409);
        }

        json_response(['success' => true, 'message' => 'บันทึกผลการพิจารณาแล้ว']);
    }

    json_response(['success' => false, 'message' => 'ไม่รองรับคำขอนี้'], 405);
} catch (PDOException $error) {
    error_log('Admin leave request database error: ' . $error->getMessage());
    json_response(['success' => false, 'message' => 'ดำเนินการกับคำขอลาไม่สำเร็จ'], 500);
}
