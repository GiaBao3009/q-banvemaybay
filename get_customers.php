<?php
require_once 'app/config/database.php';

header('Content-Type: application/json'); // Đảm bảo phản hồi JSON đúng

try {
    // Lấy tất cả dữ liệu từ bảng users (ngoại trừ password)
    $stmt = $pdo->query("SELECT id, username, full_name, email, phone, bank_name, bank_account_number, created_at FROM users");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $customers]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi lấy danh sách khách hàng!', 'error' => $e->getMessage()]);
}
?>
