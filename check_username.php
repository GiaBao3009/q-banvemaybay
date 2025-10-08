<?php
require 'app/config/database.php'; // Kết nối PDO

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');

    if (empty($username)) {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập tên đăng nhập!']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo json_encode(['status' => 'valid', 'message' => 'Tên đăng nhập hợp lệ!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tên đăng nhập không tồn tại!']);
    }
}
?>