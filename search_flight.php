<?php
session_start();
require 'app/config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['customer_username'])) {
    header('Location: customer_auth.php');
    exit;
}

// Lấy thông tin từ session
$username = $_SESSION['customer_username'];

// Lấy thông tin booking mới nhất từ bảng bookings
$stmt = $pdo->prepare("
    SELECT ticket_type, departure_date, return_date, departure, destination, adults, children, infants
    FROM bookings 
    WHERE name = ? 
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt->execute([$username]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if ($booking) {
    $_SESSION['ticket_type'] = $booking['ticket_type'];
    $_SESSION['departure_date'] = $booking['departure_date'];
    $_SESSION['return_date'] = $booking['return_date'];
    $_SESSION['departure'] = $booking['departure'];
    $_SESSION['destination'] = $booking['destination'];
    $_SESSION['adults'] = $booking['adults'];
    $_SESSION['children'] = $booking['children'];
    $_SESSION['infants'] = $booking['infants'];
} else {
    // Nếu không có dữ liệu trong bookings, kiểm tra session
    if (!isset($_SESSION['departure']) || !isset($_SESSION['destination'])) {
        header('Location: booking.php');
        exit;
    }
}

$ticket_type = $_SESSION['ticket_type'] ?? 'one_way';
$departure_date = $_SESSION['departure_date'] ?? '';
$return_date = $_SESSION['return_date'] ?? '';
$departure = $_SESSION['departure'] ?? '';
$destination = $_SESSION['destination'] ?? '';
$adults = $_SESSION['adults'] ?? 1;
$children = $_SESSION['children'] ?? 0;
$infants = $_SESSION['infants'] ?? 0;

// Xử lý khi chọn chuyến bay và nhấn "Thanh toán"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['selected_flight'])) {
    $selected_flight_id = $_POST['selected_flight'];
    $stmt = $pdo->prepare("
        SELECT airline_name, airline_code, flight_start_time, flight_end_time, ticket_price 
        FROM airlines 
        WHERE airline_id = ?
    ");
    $stmt->execute([$selected_flight_id]);
    $flight = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($flight) {
        $_SESSION['flight_info'] = [
            'airline_name' => $flight['airline_name'],
            'airline_code' => $flight['airline_code'],
            'flight_start_time' => $flight['flight_start_time'],
            'flight_end_time' => $flight['flight_end_time'],
            'ticket_price' => $flight['ticket_price']
        ];
        header('Location: invoice.php');
        exit;
    } else {
        echo "<script>alert('Không tìm thấy chuyến bay được chọn. Vui lòng thử lại.');</script>";
    }
}

// Lấy danh sách 5 hãng hàng không
try {
    $stmt = $pdo->prepare("
        SELECT 
            MIN(airline_id) AS airline_id,
            airline_name,
            airline_code,
            country
        FROM airlines
        GROUP BY airline_name, airline_code, country
    ");
    $stmt->execute();
    $airlines = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}

// Danh sách logo cho các hãng
$airline_logos = [
    'VN' => 'assets/images/unnamed.jpg',
    'VJ' => 'assets/images/photo-4-large-1721699537188.jpeg',
    'TG' => 'assets/images/ve-may-bay-thai-airways-international-1512024-4.jpg',
    'SQ' => 'assets/images/105238964-A350-ULR_RR_SIA_V04-HI-RES.jpg',
    'EK' => 'assets/images/1de3aa2a-al-EK-15f96adad17.jpg',
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chọn Hãng Hàng Không - SEVEN AIRLINE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Giữ nguyên CSS của bạn */
        body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: url('assets/images/5d7e1ce1baca7839954d4b278a87cb74.jpg') no-repeat center center fixed;
        background-size: cover;
        color: #333;
        overflow-x: hidden;
    }
    .header {
        background-color: rgba(255,255,255,0.85);
        padding: 10px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        animation: fadeInDown 0.8s ease-out;
    }
    @keyframes fadeInDown {
        0% { opacity: 0; transform: translateY(-20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .logo {
        font-size: 24px;
        font-weight: bold;
        color: #007bff;
        animation: bounceIn 1s ease-out;
    }
    @keyframes bounceIn {
        0% { opacity: 0; transform: scale(0.3); }
        50% { opacity: 1; transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    .menu {
        list-style: none;
        display: flex;
        padding: 0;
    }
    .menu li {
        margin: 0 15px;
        position: relative;
    }
    .menu li a {
        text-decoration: none;
        color: #333;
        font-weight: bold;
        transition: all 0.3s ease;
        display: inline-block;
    }
    .menu li a:hover {
        color: #007bff;
        transform: scale(1.05);
    }
    .menu li a::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        background: #007bff;
        bottom: -5px;
        left: 0;
        transition: width 0.3s ease;
    }
    .menu li a:hover::after {
        width: 100%;
    }
    .content {
        max-width: 900px;
        margin: 20px auto;
        padding: 30px;
        background-color: rgba(255,255,255,0.85);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        text-align: center;
        animation: fadeInUp 0.8s ease-out;
    }
    @keyframes fadeInUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .flight-list {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
    }
    .flight-item {
        width: 45%;
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        opacity: 0;
        animation: slideIn 0.5s ease-out forwards;
    }
    .flight-item:nth-child(1) { animation-delay: 0.1s; }
    .flight-item:nth-child(2) { animation-delay: 0.2s; }
    .flight-item:nth-child(3) { animation-delay: 0.3s; }
    .flight-item:nth-child(4) { animation-delay: 0.4s; }
    .flight-item:nth-child(5) { animation-delay: 0.5s; }
    @keyframes slideIn {
        0% { opacity: 0; transform: translateX(-20px); }
        100% { opacity: 1; transform: translateX(0); }
    }
    .flight-item:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    }
    .flight-item img {
        width: 100px;
        height: 100px;
        object-fit: contain;
        border-radius: 8px;
    }
    .flight-item h3 {
        margin: 10px 0;
        color: #007bff;
    }
    .flight-item p {
        margin: 5px 0;
        font-size: 14px;
    }
    .select-radio {
        position: absolute;
        top: 10px;
        left: 10px;
        transform: scale(1.3);
    }
    .btn-container {
        margin-top: 20px;
    }
    .btn-back, .btn-book {
        display: inline-block;
        text-decoration: none;
        padding: 12px 20px;
        border-radius: 6px;
        font-weight: bold;
        transition: all 0.3s ease;
        color: #fff;
        border: none;
        cursor: pointer;
    }
    .btn-back {
        background-color: #3498db;
        margin-right: 10px;
    }
    .btn-book {
        background-color: #28a745;
    }
    .btn-back:hover, .btn-book:hover {
        transform: scale(1.05);
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }
    .btn-back:hover {
        background-color: #2980b9;
    }
    .btn-book:hover {
        background-color: #218838;
    }
    #flight-detail {
        margin-top: 30px;
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        animation: fadeIn 0.5s ease-out;
    }
    #flight-detail h3 {
        font-size: 1.6rem;
        color: #007bff;
        margin-bottom: 15px;
    }
    #flight-detail table {
        width: 100%;
        border-collapse: collapse;
        border-radius: 8px;
        overflow: hidden;
    }
    #flight-detail th, #flight-detail td {
        padding: 15px;
        text-align: center;
        font-size: 14px;
    }
    #flight-detail th {
        background-color: #3498db;
        color: #fff;
        font-weight: bold;
    }
    #flight-detail td {
        background-color: #f7f7f7;
        transition: background-color 0.3s ease;
    }
    #flight-detail tr:nth-child(even) td {
        background-color: #ebebeb;
    }
    #flight-detail tr:hover td {
        background-color: #d1e7fd;
    }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">SEVEN AIRLINE</div>
        <ul class="menu">
            <li><a href="account.php"><i class="fas fa-user-circle"></i> Tài khoản</a></li>
            <li><a href="booking.php"><i class="fas fa-ticket-alt"></i> Đặt vé</a></li>
            <li><a href="history.php"><i class="fas fa-history"></i> Lịch sử vé</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
        </ul>
        <div class="user-info">👤 <?= htmlspecialchars($username) ?></div>
    </header>

    <div class="content">
        <h2>Chọn hãng hàng không</h2>
        <form id="flight-form" method="POST" action="search_flight.php">
            <div class="flight-list">
                <?php foreach ($airlines as $airline): ?>
                    <?php $logo_path = $airline_logos[$airline['airline_code']] ?? 'assets/images/default_airline.png'; ?>
                    <label class="flight-item">
                        <input type="radio" name="selected_airline" value="<?= $airline['airline_id'] ?>" class="select-radio" required>
                        <img src="<?= $logo_path ?>" alt="Logo hãng bay">
                        <h3><?= htmlspecialchars($airline['airline_name']) ?> (<?= htmlspecialchars($airline['airline_code']) ?>)</h3>
                        <p><strong>Quốc gia:</strong> <?= htmlspecialchars($airline['country']) ?></p>
                    </label>
                <?php endforeach; ?>
            </div>

            <div id="flight-detail"></div>

            <div class="btn-container">
                <a href="booking.php" class="btn-back"><i class="fas fa-arrow-left"></i> Quay lại</a>
                <button type="submit" class="btn-back btn-book" id="submit-btn" style="display: none;">Thanh toán</button>
            </div>
        </form>
    </div>

    <script>
document.querySelectorAll('input[name="selected_airline"]').forEach(radio => {
    radio.addEventListener('change', function () {
        const selectedId = this.value;
        const departureDate = '<?= $departure_date ?>';
        const returnDate = '<?= $return_date ?>';
        const ticketType = '<?= $ticket_type ?>';

        // Ẩn tất cả flight items ngoại trừ cái được chọn
        document.querySelectorAll('.flight-item').forEach(item => {
            if (item.querySelector('input').value !== selectedId) {
                item.style.display = 'none';
            }
        });

        // Gửi yêu cầu AJAX để lấy danh sách chuyến bay
        fetch(`get_flights.php?airline_id=${selectedId}&departure=${'<?= $departure ?>'}&destination=${'<?= $destination ?>'}`)
            .then(res => {
                if (!res.ok) throw new Error('Lỗi khi tải dữ liệu chuyến bay');
                return res.json();
            })
            .then(data => {
                if (data.error) throw new Error(data.error);
                let html = `
                    <h3>Chọn giờ khởi hành và giá vé:</h3>
                    <table border="1" cellpadding="10" cellspacing="0" style="margin: 0 auto; background: #fff; border-radius: 8px;">
                        <tr>
                            <th>Ngày đi</th>
                            <th>Ngày về</th>
                            <th>Giờ khởi hành</th>
                            <th>Giờ đến</th>
                            <th>Giá vé</th>
                            <th>Chọn</th>
                        </tr>
                `;
                data.forEach(flight => {
                    html += `
                        <tr>
                            <td>${departureDate || 'Không xác định'}</td>
                            <td>${ticketType === 'round_trip' && returnDate ? returnDate : ''}</td>
                            <td>${flight.flight_start_time}</td>
                            <td>${flight.flight_end_time}</td>
                            <td>${Number(flight.ticket_price).toLocaleString()} đ</td>
                            <td><input type="radio" name="selected_flight" value="${flight.airline_id}" required></td>
                        </tr>
                    `;
                });
                html += '</table>';
                document.getElementById('flight-detail').innerHTML = html;

                // Hiển thị nút "Thanh toán"
                document.getElementById('submit-btn').style.display = 'inline-block';
            })
            .catch(error => {
                console.error('Lỗi:', error);
                alert('Không thể tải danh sách chuyến bay: ' + error.message);
            });
    });
});
    </script>
</body>
</html>