<?php
require_once 'app/config/database.php';
session_start();

// Kiểm tra xem admin đã đăng nhập chưa

// Truy vấn dữ liệu từ bảng users
$stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Khách Hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: url('assets/images/hinh-nen-iphone-may-bay-cuc-chat-cho-dan-dam-me-xe-dich-230427090116.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 1000px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        h2 {
            color: #1E90FF;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #1E90FF;
            color: white;
        }
        .btn {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
        }
        .edit-btn {
            background: #FFD700;
            color: black;
        }
        .delete-btn {
            background: #FF4D4D;
            color: white;
        }
        .btn i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Quản Lý Khách Hàng</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Tên đăng nhập</th>
                <th>Họ và tên</th>
                <th>Email</th>
                <th>Số điện thoại</th>
                <th>Ngân hàng</th>
                <th>Số tài khoản</th>
                <th>Hành động</th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['phone']) ?></td>
                    <td><?= htmlspecialchars($user['bank_name']) ?></td>
                    <td><?= htmlspecialchars($user['bank_account_number']) ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn edit-btn"><i class="fas fa-edit"></i> Sửa</a>
                        <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn delete-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa khách hàng này?')">
                            <i class="fas fa-trash"></i> Xóa
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>