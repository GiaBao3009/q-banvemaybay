<?php
session_start();
require_once 'app/config/database.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.']);
    exit;
}

// Kiểm tra nếu có ID được gửi từ AJAX
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID khách hàng.']);
    exit;
}

$customerId = intval($_POST['id']); // Chuyển đổi ID thành số nguyên để tránh lỗi SQL Injection

try {
    // Xóa khách hàng khỏi bảng `users`
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$customerId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Khách hàng đã được xoá.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy khách hàng hoặc lỗi khi xoá.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi xoá khách hàng.', 'error' => $e->getMessage()]);
}
?>
