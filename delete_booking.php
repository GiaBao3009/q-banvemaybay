<?php
require_once 'app/config/database.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}

try {
    $query = "DELETE FROM hoadon WHERE ma_hoadon = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>