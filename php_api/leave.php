<?php
require_once 'api_common.php';
configure_api();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'ไม่รองรับคำขอนี้'], 405);
}

require_role('user');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        require 'condb.php';
        $stmt = $conn->prepare(
            'SELECT id, employee_name, department, leave_type, start_date, end_date, total_days, reason, attachment_name, status, created_at '
            . 'FROM leave_requests WHERE user_id = ? ORDER BY created_at DESC, id DESC'
        );
        $stmt->execute([(int) $_SESSION['account_id']]);
        json_response(['success' => true, 'requests' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (PDOException $error) {
        error_log('Leave history database error: ' . $error->getMessage());
        json_response(['success' => false, 'message' => 'โหลดประวัติการลาไม่สำเร็จ'], 500);
    }
}

$name = (string) $_SESSION['account_full_name'];
$department = trim(isset($_POST['department']) ? (string) $_POST['department'] : '');
$type = isset($_POST['type']) ? (string) $_POST['type'] : '';
$start_input = isset($_POST['startDate']) ? (string) $_POST['startDate'] : '';
$end_input = isset($_POST['endDate']) ? (string) $_POST['endDate'] : '';
$reason = trim(isset($_POST['reason']) ? (string) $_POST['reason'] : '');
$allowed_types = ['ลาป่วย', 'ลากิจ', 'ลาพักร้อน', 'ลาอื่น ๆ'];

if ($name === '' || $department === '' || $reason === '' || $start_input === '' || $end_input === '') {
    json_response(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบทุกช่อง'], 400);
}

if (!in_array($type, $allowed_types, true)) {
    json_response(['success' => false, 'message' => 'ประเภทการลาไม่ถูกต้อง'], 400);
}

$start_date = DateTimeImmutable::createFromFormat('!Y-m-d', $start_input);
$start_errors = DateTimeImmutable::getLastErrors();
$end_date = DateTimeImmutable::createFromFormat('!Y-m-d', $end_input);
$end_errors = DateTimeImmutable::getLastErrors();

if (
    !$start_date || !$end_date ||
    ($start_errors !== false && ($start_errors['warning_count'] || $start_errors['error_count'])) ||
    ($end_errors !== false && ($end_errors['warning_count'] || $end_errors['error_count'])) ||
    $start_date->format('Y-m-d') !== $start_input ||
    $end_date->format('Y-m-d') !== $end_input
) {
    json_response(['success' => false, 'message' => 'รูปแบบวันที่ไม่ถูกต้อง'], 400);
}

if ($end_date < $start_date) {
    json_response(['success' => false, 'message' => 'วันที่สิ้นสุดต้องไม่ก่อนวันที่เริ่มลา'], 400);
}

$total_days = (int) $start_date->diff($end_date)->days + 1;
$attachment_name = null;
$attachment_mime = null;
$attachment_data = null;

if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
    $upload = $_FILES['attachment'];
    if ($upload['error'] !== UPLOAD_ERR_OK) {
        json_response(['success' => false, 'message' => 'อัปโหลดไฟล์ไม่สำเร็จ'], 400);
    }

    if ($upload['size'] > 5 * 1024 * 1024) {
        json_response(['success' => false, 'message' => 'ไฟล์แนบต้องมีขนาดไม่เกิน 5 MB'], 400);
    }

    $file_info = new finfo(FILEINFO_MIME_TYPE);
    $detected_mime = $file_info->file($upload['tmp_name']);
    $allowed_mime_types = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!in_array($detected_mime, $allowed_mime_types, true)) {
        json_response(['success' => false, 'message' => 'รองรับเฉพาะไฟล์ PDF, JPG และ PNG'], 400);
    }

    $attachment_name = basename((string) $upload['name']);
    $attachment_mime = $detected_mime;
    $attachment_data = file_get_contents($upload['tmp_name']);
    if ($attachment_data === false) {
        json_response(['success' => false, 'message' => 'อ่านไฟล์แนบไม่สำเร็จ'], 400);
    }
}

try {
    require 'condb.php';
    $stmt = $conn->prepare(
        'INSERT INTO leave_requests '
        . '(user_id, employee_name, department, leave_type, start_date, end_date, total_days, reason, attachment_name, attachment_mime, attachment_data) '
        . 'VALUES (:user_id, :employee_name, :department, :leave_type, :start_date, :end_date, :total_days, :reason, :attachment_name, :attachment_mime, :attachment_data)'
    );
    $stmt->bindValue(':user_id', (int) $_SESSION['account_id'], PDO::PARAM_INT);
    $stmt->bindValue(':employee_name', $name);
    $stmt->bindValue(':department', $department);
    $stmt->bindValue(':leave_type', $type);
    $stmt->bindValue(':start_date', $start_input);
    $stmt->bindValue(':end_date', $end_input);
    $stmt->bindValue(':total_days', $total_days, PDO::PARAM_INT);
    $stmt->bindValue(':reason', $reason);
    $stmt->bindValue(':attachment_name', $attachment_name, $attachment_name === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':attachment_mime', $attachment_mime, $attachment_mime === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':attachment_data', $attachment_data, $attachment_data === null ? PDO::PARAM_NULL : PDO::PARAM_LOB);
    $stmt->execute();

    json_response([
        'success' => true,
        'message' => 'ส่งคำขอลาสำเร็จแล้ว',
        'request' => ['id' => (int) $conn->lastInsertId(), 'status' => 'pending'],
    ], 201);
} catch (PDOException $error) {
    error_log('Leave request database error: ' . $error->getMessage());
    json_response(['success' => false, 'message' => 'บันทึกคำขอลาไม่สำเร็จ กรุณาตรวจสอบฐานข้อมูล'], 500);
}
