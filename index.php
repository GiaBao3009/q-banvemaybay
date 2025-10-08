<?php
session_start();
include('app/config/database.php');

// Kiểm tra nếu chưa đăng nhập thì chuyển hướng
if (!isset($_SESSION['username'])) {
    header('Location: login_register.php');
    exit;
}

// Xử lý tìm kiếm chuyến bay
$departure = $_POST['departure'] ?? '';
$arrival = $_POST['arrival'] ?? '';
$date = $_POST['date'] ?? '';

$flights = [];

if ($departure && $arrival && $date) {
    $query = "SELECT f.flight_code, f.departure_time, f.arrival_time, f.price, 
                     da.name AS departure_airport, aa.name AS arrival_airport
              FROM flights f
              JOIN airports da ON f.departure_airport_id = da.id
              JOIN airports aa ON f.arrival_airport_id = aa.id
              WHERE da.name LIKE :departure
              AND aa.name LIKE :arrival
              AND DATE(f.departure_time) = :date";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':departure', "%$departure%", PDO::PARAM_STR);
    $stmt->bindValue(':arrival', "%$arrival%", PDO::PARAM_STR);
    $stmt->bindValue(':date', $date, PDO::PARAM_STR);
    $stmt->execute();
    $flights = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Bán Vé Máy Bay</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>Hệ Thống Quản Lý Bán Vé Máy Bay</h1>
        <nav>
            <a href="index.php">Trang Chủ</a>
            <a href="search_flights.php">Tìm Chuyến Bay</a>
            <a href="book_ticket.php">Đặt Vé</a>
            <a href="contact.php">Liên Hệ</a>
            <a href="logout.php">Đăng Xuất</a>
        </nav>
    </header>

    <main>
        <section class="search-section">
            <h2>Tìm Chuyến Bay</h2>
            <form action="index.php" method="POST">
                <label for="departure">Sân bay khởi hành:</label>
                <input type="text" name="departure" id="departure" value="<?= htmlspecialchars($departure) ?>" required>

                <label for="arrival">Sân bay đến:</label>
                <input type="text" name="arrival" id="arrival" value="<?= htmlspecialchars($arrival) ?>" required>

                <label for="date">Ngày bay:</label>
                <input type="date" name="date" id="date" value="<?= htmlspecialchars($date) ?>" required>

                <button type="submit">Tìm Kiếm</button>
            </form>
        </section>

        <section class="flight-results">
            <?php if (!empty($flights)): ?>
                <h2>Kết quả tìm kiếm:</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Mã chuyến bay</th>
                            <th>Sân bay khởi hành</th>
                            <th>Sân bay đến</th>
                            <th>Thời gian khởi hành</th>
                            <th>Thời gian đến</th>
                            <th>Giá vé</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($flights as $flight): ?>
                            <tr>
                                <td><?= htmlspecialchars($flight['flight_code']) ?></td>
                                <td><?= htmlspecialchars($flight['departure_airport']) ?></td>
                                <td><?= htmlspecialchars($flight['arrival_airport']) ?></td>
                                <td><?= htmlspecialchars($flight['departure_time']) ?></td>
                                <td><?= htmlspecialchars($flight['arrival_time']) ?></td>
                                <td><?= number_format($flight['price']) ?> VNĐ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Không tìm thấy chuyến bay nào phù hợp.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 Hệ Thống Quản Lý Bán Vé Máy Bay.</p>
    </footer>
</body>
</html>
