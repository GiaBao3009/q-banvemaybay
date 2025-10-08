<?php
session_start();
require_once 'app/config/database.php';

// Kiểm tra nếu chưa đăng nhập hoặc không phải admin thì chuyển hướng về trang đăng nhập
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login_register.php');
    exit;
}

// Truy vấn danh sách nhân viên
try {
    $stmt = $pdo->prepare("SELECT id, full_name, username, email, phone, role, created_at FROM staff WHERE role = 'staff'");
    $stmt->execute();
    $staffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ Lỗi truy vấn dữ liệu: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nhân viên</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin_style.css">
</head>
<body>

<header>
    <h1 style="text-align: center;">Quản lý nhân viên</h1>
</header>

<main class="container">
    <table border="1" width="100%" cellspacing="0" cellpadding="10">
    <thead>
    <tr>
        <th>ID</th>
        <th>Họ tên</th>
        <th>Tên đăng nhập</th>
        <th>Email</th>
        <th>Số điện thoại</th>
        <th>Vai trò</th>
        <th>Ngày tạo</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!empty($staffs)) { ?>
        <?php foreach ($staffs as $row) { ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['full_name'] ?: 'Chưa cập nhật') ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email'] ?: 'Chưa cập nhật') ?></td>
                <td><?= htmlspecialchars($row['phone'] ?: 'Chưa cập nhật') ?></td>
                <td><?= htmlspecialchars($row['role']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <tr><td colspan="7" style="text-align: center;">Không có nhân viên nào</td></tr>
    <?php } ?>
    </tbody>
    </table>
</main>

</body>
</html>
