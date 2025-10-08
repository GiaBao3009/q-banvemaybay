<?php
require 'app/config/database.php'; // Kết nối PDO

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        echo "Vui lòng nhập đầy đủ thông tin!";
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE username = ?');
    $success = $stmt->execute([$hashedPassword, $username]);

    if ($success) {
        echo "Cập nhật mật khẩu thành công! Vui lòng đăng nhập lại.";
    } else {
        echo "Cập nhật mật khẩu thất bại! Vui lòng thử lại.";
    }
}
?>