<?php
session_start();
require_once 'app/config/database.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền truy cập.']);
    exit;
}

try {
    // Truy vấn tất cả nhân viên từ bảng `staff`
    $stmt = $pdo->query("SELECT id, full_name, username, email, phone, role, created_at FROM staff ORDER BY id DESC");
    $staffs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $staffs]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi truy vấn dữ liệu!', 'error' => $e->getMessage()]);
}
?>
