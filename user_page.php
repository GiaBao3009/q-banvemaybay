<?php 
session_start(); 
if (!isset($_SESSION['customer_username'])) {
    header('Location: customer_auth.php');
    exit;
}
$username = $_SESSION['customer_username'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEVEN AIRLINE - Trang Chủ</title>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url('assets/images/5d7e1ce1baca7839954d4b278a87cb74.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        .content {
            flex: 1;
            padding: 60px 40px;
        }

        /* Navigation styles */
        .nav {
            display: flex;
            justify-content: center;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 10px 0;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .nav a {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease-in-out;
            border-radius: 8px;
            margin: 0 10px;
        }

        .nav a i {
            margin-right: 8px;
            color: #007bff;
        }

        .nav a:hover {
            background-color: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        header {
            background-color: rgba(255, 255, 255, 0.85);
            border-radius: 16px;
            padding: 40px 20px;
            margin-bottom: 40px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            border: 1px solid #ddd;
            text-align: center;
        }

        header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            background: linear-gradient(90deg, #007bff, #00c6ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        header h1 i {
            margin-right: 10px;
            color: #007bff;
            animation: fly 2s infinite alternate ease-in-out;
        }

        @keyframes fly {
            0% { transform: translateY(0); }
            100% { transform: translateY(-5px); }
        }

        @media (max-width: 768px) {
            .nav {
                flex-direction: column;
                align-items: center;
            }

            .nav a {
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>

<div class="layout">
    <!-- MAIN CONTENT -->
    <div class="content">
        <!-- Navigation -->
        <div class="nav">
            <a href="account.php"><i class="fas fa-user-circle"></i> Thông tin tài khoản</a>
            <a href="booking.php"><i class="fas fa-ticket-alt"></i> Đặt vé</a>
            <a href="history.php"><i class="fas fa-history"></i> Lịch sử vé</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>

        <header>
            <h1>
                <i class="fas fa-plane-departure"></i>
                Chào mừng, <?= htmlspecialchars($username) ?> đến với SEVEN AIRLINE
            </h1>
        </header>

        <!-- Nội dung khác ở đây -->

    </div>
</div>

</body>
</html>