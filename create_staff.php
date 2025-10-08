<?php
session_start();
require_once 'app/config/database.php';

header('Content-Type: application/json');

// Kiểm tra quyền admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["success" => false, "message" => "Bạn không có quyền truy cập!"]);
    exit;
}

// Sinh username ngẫu nhiên
$username = 'staff_' . rand(1000, 9999);
$password = '123'; // Mật khẩu mặc định
$hashed_password = password_hash($password, PASSWORD_BCRYPT); // Mã hóa mật khẩu

try {
    // Thêm nhân viên mới vào database (chỉ thêm username & password)
    $stmt = $pdo->prepare("INSERT INTO staff (username, password, role, created_at) VALUES (?, ?, 'staff', NOW())");
    $stmt->execute([$username, $hashed_password]);

    // Kiểm tra xem có thành công không
    if ($stmt->rowCount() > 0) {
        // Lấy ID vừa tạo
        $staff_id = $pdo->lastInsertId();
        $created_at = date("Y-m-d H:i:s");

        echo json_encode([
            "success" => true,
            "message" => "✅ Tạo tài khoản thành công!\nUsername: $username\nPassword: $password",
            "id" => $staff_id,
            "username" => $username,
            "created_at" => $created_at
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "❌ Lỗi khi thêm tài khoản!"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "❌ Lỗi tạo tài khoản: " . $e->getMessage()]);
}
?>
