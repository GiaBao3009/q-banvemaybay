<?php
require 'app/config/database.php'; // Kết nối PDO

header('Content-Type: application/json');

$username = $_POST['username'] ?? '';

$stmt = $pdo->prepare('SELECT * FROM staff WHERE username = ?');
$stmt->execute([$username]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);

if ($staff) {
    echo json_encode(['status' => 'valid', 'message' => 'Tên đăng nhập hợp lệ']);
} else {
    echo json_encode(['status' => 'invalid', 'message' => 'Tên đăng nhập không tồn tại trong danh sách nhân viên']);
}
?>