<?php 
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'staff') {
    header("Location: login_staff.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Nhân Viên</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Xin chào, <?php echo $_SESSION['full_name']; ?>!</h1>
        </div>
    </header>

    <main>
        <section class="dashboard">
            <h2>Chức Năng Nhân Viên</h2>
            <ul>
                <li><a href="manage_tickets.php">Quản lý vé</a></li>
                <li><a href="view_flights.php">Xem chuyến bay</a></li>
                <li><a href="logout.php">Đăng xuất</a></li>
            </ul>
        </section>
    </main>
</body>
</html>
