<?php
require 'app/config/database.php'; // Kết nối PDO

$username = $_POST['username'] ?? '';
$newPassword = $_POST['password'] ?? '';

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('UPDATE staff SET password = ? WHERE username = ?');
$isUpdated = $stmt->execute([$hashedPassword, $username]);

if ($isUpdated) {
    echo "Cập nhật mật khẩu thành công";
} else {
    echo "Cập nhật mật khẩu thất bại";
}
?>