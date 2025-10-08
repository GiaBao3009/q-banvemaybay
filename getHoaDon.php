<?php
require_once 'app/config/database.php';
header('Content-Type: application/json');

// Kiểm tra kết nối database
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
    exit;
}

try {
    $query = "SELECT * FROM hoadon";
    $stmt = $pdo->query($query);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($bookings) {
        echo json_encode(['success' => true, 'data' => $bookings]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không có dữ liệu trong bảng hoadon']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>