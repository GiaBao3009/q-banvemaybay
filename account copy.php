<?php 
session_start(); 
require 'app/config/database.php'; // Kết nối PDO tới database

// Nếu chưa đăng nhập thì đưa về trang đăng nhập
if (!isset($_SESSION['customer_username'])) {
    header('Location: customer_auth.php');
    exit;
}
$username = $_SESSION['customer_username'];

// Xử lý khi người dùng bấm nút "Cập nhật"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $bank_name = $_POST['bank_name'];
    $bank_account_number = $_POST['bank_account_number'];

    // Cập nhật thông tin vào database
    $stmt = $pdo->prepare('
        UPDATE users SET 
            full_name = ?, 
            email = ?, 
            phone = ?, 
            bank_name = ?, 
            bank_account_number = ?
        WHERE username = ?
    ');
    $updated = $stmt->execute([
        $full_name,
        $email,
        $phone,
        $bank_name,
        $bank_account_number,
        $username
    ]);

    if ($updated) {
        $success_message = "Cập nhật thông tin thành công!";
    } else {
        $error_message = "Có lỗi xảy ra khi cập nhật thông tin!";
    }
}

// Lấy thông tin mới nhất từ database
$stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Không tìm thấy tài khoản!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông Tin Tài Khoản - SEVEN AIRLINE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- FontAwesome để sử dụng icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Thiết lập cơ bản */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url('assets/images/5d7e1ce1baca7839954d4b278a87cb74.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
        }
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(255, 255, 255, 0.5);
            z-index: 0;
        }
        /* Header với menu ngang */
        .header {
            background-color: rgba(255,255,255,0.85);
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            position: relative;
            z-index: 1;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .menu li {
            margin: 0 15px; /* Khoảng cách đều giữa các mục */
        }
        .menu li a {
            text-decoration: none;
            color: #333;
            font-weight: 600;
            transition: color 0.3s;
        }
        .menu li a:hover {
            color: #007bff;
        }
        .user-info {
            font-size: 16px;
        }
        /* Nội dung chính */
        .content {
            max-width: 800px;
            margin: 20px auto;
            padding: 40px;
            background-color: rgba(255,255,255,0.85);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            position: relative;
            z-index: 1;
        }
        .content h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 28px;
            color: #007bff;
        }
        .info-item {
            margin: 12px 0;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        .info-item label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .info-item input {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .btn-back, .btn-submit {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 6px;
            transition: background 0.3s;
        }
        .btn-back {
            background-color: #3498db;
            color: #fff;
        }
        .btn-submit {
            background-color: #28a745;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .btn-back:hover {
            background-color: #2980b9;
        }
        .btn-submit:hover {
            background-color: #218838;
        }
        .message {
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 6px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }
            .menu {
                flex-direction: column;
                gap: 10px;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER với menu ngang -->
    <header class="header">
        <div class="logo">SEVEN AIRLINE</div>
        <ul class="menu">
            <li><a href="account.php"><i class="fas fa-user-circle"></i> Thông tin tài khoản</a></li>
            <li><a href="booking.php"><i class="fas fa-ticket-alt"></i> Đặt vé</a></li>
            <li><a href="history.php"><i class="fas fa-history"></i> Lịch sử vé</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
        </ul>
        <div class="user-info">👤 <?= htmlspecialchars($username) ?></div>
    </header>

    <!-- Nội dung chính -->
    <div class="content">
        <h1><i class="fas fa-user-circle"></i> Thông Tin Tài Khoản</h1>

        <!-- Thông báo thành công hoặc lỗi -->
        <?php if (!empty($success_message)) : ?>
            <div class="message success"><?= $success_message ?></div>
        <?php elseif (!empty($error_message)) : ?>
            <div class="message error"><?= $error_message ?></div>
        <?php endif; ?>

        <!-- FORM CẬP NHẬT -->
        <form method="POST" action="">
            <div class="info-item">
                <label>Tên đăng nhập:</label>
                <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled>
            </div>
            <div class="info-item">
                <label for="full_name">Họ và tên:</label>
                <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
            </div>
            <div class="info-item">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="info-item">
                <label for="phone">Số điện thoại:</label>
                <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
            </div>
            <div class="info-item">
                <label for="bank_name">Ngân hàng:</label>
                <input type="text" id="bank_name" name="bank_name" value="<?= htmlspecialchars($user['bank_name']) ?>">
            </div>
            <div class="info-item">
                <label for="bank_account_number">Số tài khoản:</label>
                <input type="text" id="bank_account_number" name="bank_account_number" value="<?= htmlspecialchars($user['bank_account_number']) ?>">
            </div>
            <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Cập nhật</button>
            <a href="user_page.php" class="btn-back"><i class="fas fa-arrow-left"></i> Quay lại</a>
        </form>
    </div>
</body>
</html> 