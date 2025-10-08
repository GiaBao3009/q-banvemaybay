<?php
session_start();
require 'app/config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['customer_username'])) {
    header('Location: customer_auth.php');
    exit;
}

// Kiểm tra thông tin chuyến bay trong session
if (!isset($_SESSION['flight_info']) || !isset($_SESSION['departure']) || !isset($_SESSION['destination'])) {
    header('Location: search_flight.php');
    exit;
}

// Biến để kiểm tra trạng thái thanh toán
$payment_confirmed = false;

// Tạo mã đặt vé độc đáo một lần duy nhất
$booking_reference = 'SA' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

// Xử lý khi nhấn nút xác nhận đặt vé
if (isset($_POST['confirm_booking'])) {
    $username = $_SESSION['customer_username'];
    $departure_date = $_SESSION['departure_date'] ?? '';
    $return_date = $_SESSION['return_date'] ?? null;
    $ticket_type = $_SESSION['ticket_type'] ?? 'one_way';
    $adults = $_SESSION['adults'] ?? 1;
    $children = $_SESSION['children'] ?? 0;
    $infants = $_SESSION['infants'] ?? 0;
    $departure = $_SESSION['departure'] ?? '';
    $destination = $_SESSION['destination'] ?? '';
    $flight = $_SESSION['flight_info'];

    // Nếu là vé một chiều, return_date là NULL
    if ($ticket_type === 'one_way') {
        $return_date = null;
    }

    // Tính tổng tiền
    $base_price = $flight['ticket_price'];
    $adult_price = $base_price * $adults;
    $child_price = $base_price * 0.75 * $children;
    $infant_price = $base_price * 0.1 * $infants;
    $total_price = $adult_price + $child_price + $infant_price;
    if ($ticket_type === 'round_trip') {
        $total_price *= 2;
    }

    // Chuẩn bị truy vấn INSERT khớp với bảng hoadon
    $stmt = $pdo->prepare("
        INSERT INTO hoadon (
            ma_hoadon, name, ticket_type, departure, destination, departure_date, return_date, 
            adults, children, infants, airline_name, airline_code, country, 
            flight_start_time, flight_end_time, ticket_price
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt_country = $pdo->prepare("SELECT country FROM airlines WHERE airline_code = ?");
$stmt_country->execute([$flight['airline_code']]);
$country_result = $stmt_country->fetch(PDO::FETCH_ASSOC);

// Lấy giá trị của country từ kết quả truy vấn
$country = $country_result ? $country_result['country'] : 'Unknown';
    // Thực thi truy vấn với dữ liệu từ session
    $stmt->execute([
        $booking_reference,
        $username, 
        $ticket_type, 
        $departure, 
        $destination, 
        $departure_date, 
        $return_date, 
        $adults, 
        $children, 
        $infants, 
        $flight['airline_name'], 
        $flight['airline_code'], 
        $country,
        $flight['flight_start_time'], 
        $flight['flight_end_time'], 
        $total_price
    ]);

    // Đánh dấu thanh toán đã hoàn tất
    $payment_confirmed = true;
}

// Lấy thông tin từ session để hiển thị
$username = $_SESSION['customer_username'];
$departure_date = $_SESSION['departure_date'] ?? '';
$return_date = $_SESSION['return_date'] ?? '';
$ticket_type = $_SESSION['ticket_type'] ?? 'one_way';
$adults = $_SESSION['adults'] ?? 1;
$children = $_SESSION['children'] ?? 0;
$infants = $_SESSION['infants'] ?? 0;
$departure = $_SESSION['departure'] ?? '';
$destination = $_SESSION['destination'] ?? '';

// Lấy thông tin chuyến bay từ session
$flight = $_SESSION['flight_info'];
$airline = [
    'airline_name' => $flight['airline_name'],
    'airline_code' => $flight['airline_code']
];

// Tính tổng số hành khách
$total_passengers = $adults + $children + $infants;

// Tính tổng tiền để hiển thị
$base_price = $flight['ticket_price'];
$adult_price = $base_price * $adults;
$child_price = $base_price * 0.75 * $children;
$infant_price = $base_price * 0.1 * $infants;
$total_price = $adult_price + $child_price + $infant_price;
if ($ticket_type === 'round_trip') {
    $total_price *= 2;
}

// Ánh xạ mã sân bay sang tên đầy đủ
$airport_names = [
    'SGN' => 'Hồ Chí Minh (SGN)', 'HAN' => 'Hà Nội (HAN)','DAD' => 'Đà Nẵng (DAD)', 'CXR' => 'Nha Trang (CXR)',
    'PQC' => 'Phú Quốc (PQC)', 'HPH' => 'Hải Phòng (HPH)', 'VCA' => 'Cần Thơ (VCA)', 'DLI' => 'Đà Lạt (DLI)',
    'BKK' => 'Bangkok (BKK)', 'KUL' => 'Kuala Lumpur (KUL)', 'SIN' => 'Singapore (SIN)', 'MNL' => 'Manila (MNL)',
    'CGK' => 'Jakarta (CGK)', 'PNH' => 'Phnom Penh (PNH)', 'RGN' => 'Yangon (RGN)', 'VTE' => 'Vientiane (VTE)',
    'NRT' => 'Tokyo Narita (NRT)', 'ICN' => 'Seoul Incheon (ICN)', 'HKG' => 'Hong Kong (HKG)', 'PEK' => 'Bắc Kinh (PEK)',
    'JFK' => 'New York JFK (JFK)', 'LAX' => 'Los Angeles (LAX)', 'YYZ' => 'Toronto Pearson (YYZ)',
    'LHR' => 'London Heathrow (LHR)', 'CDG' => 'Paris Charles de Gaulle (CDG)', 'FRA' => 'Frankfurt (FRA)',
    'AMS' => 'Amsterdam (AMS)', 'MAD' => 'Madrid (MAD)', 'FCO' => 'Rome Fiumicino (FCO)', 'BER' => 'Berlin Brandenburg (BER)',
    'VIE' => 'Vienna (VIE)', 'ZRH' => 'Zurich (ZRH)',
];
$departure_name = $airport_names[$departure] ?? $departure;
$destination_name = $airport_names[$destination] ?? $destination;

// Danh sách logo cho các hãng
$airline_logos = [
    'VN' => 'assets/images/unnamed.jpg',
    'VJ' => 'assets/images/photo-4-large-1721699537188.jpeg',
    'TG' => 'assets/images/ve-may-bay-thai-airways-international-1512024-4.jpg',
    'SQ' => 'assets/images/105238964-A350-ULR_RR_SIA_V04-HI-RES.jpg',
    'EK' => 'assets/images/1de3aa2a-al-EK-15f96adad17.jpg',
];
$logo_path = $airline_logos[$airline['airline_code']] ?? 'assets/images/default_airline.png';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa Đơn Đặt Vé - SEVEN AIRLINE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: url('assets/images/5d7e1ce1baca7839954d4b278a87cb74.jpg') no-repeat center center fixed;
        background-size: cover;
        color: #333;
        overflow-x: hidden;
    }
    body::before {
        content: "";
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background-color: rgba(255, 255, 255, 0.5);
        z-index: 0;
    }
    .header {
        background-color: rgba(255,255,255,0.85);
        padding: 10px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        position: relative;
        z-index: 1;
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
        margin: 0;
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
    .invoice-container {
        max-width: 1000px;
        margin: 30px auto;
        background-color: rgba(255,255,255,0.9);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        position: relative;
        z-index: 1;
        padding: 0;
        overflow: hidden;
        animation: fadeInUp 0.8s ease-out;
    }
    @keyframes fadeInUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .invoice-header {
        background: linear-gradient(90deg, #007bff, #0056b3);
        color: white;
        padding: 20px;
        text-align: center;
        position: relative;
        animation: fadeIn 0.5s ease-out;
    }
    .invoice-header h1 {
        margin: 0;
        font-size: 28px;
    }
    .booking-ref {
        position: absolute;
        top: 10px;
        right: 15px;
        background-color: rgba(255,255,255,0.2);
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 14px;
    }
    .invoice-body {
        padding: 20px;
    }
    .section {
        margin-bottom: 25px;
        border-bottom: 1px solid #eee;
        padding-bottom: 20px;
        opacity: 0;
        animation: slideIn 0.5s ease-out forwards;
    }
    .section:nth-child(1) { animation-delay: 0.1s; }
    .section:nth-child(2) { animation-delay: 0.2s; }
    .section:nth-child(3) { animation-delay: 0.3s; }
    .section:nth-child(4) { animation-delay: 0.4s; }
    @keyframes slideIn {
        0% { opacity: 0; transform: translateX(-20px); }
        100% { opacity: 1; transform: translateX(0); }
    }
    .section:last-child {
        border-bottom: none;
    }
    .section-title {
        color: #007bff;
        font-size: 20px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }
    .section-title i {
        margin-right: 10px;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    .info-item {
        display: flex;
        flex-direction: column;
    }
    .info-label {
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
    }
    .info-value {
        font-size: 16px;
        font-weight: bold;
    }
    .flight-details {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        background-color: #f9f9f9;
        padding: 15px;
        border-radius: 8px;
        transition: transform 0.3s ease;
    }
    .flight-details:hover {
        transform: scale(1.02);
    }
    .airline-logo {
        width: 80px;
        height: 80px;
        object-fit: contain;
        border-radius: 8px;
        margin-right: 20px;
    }
    .flight-route {
        flex: 1;
    }
    .flight-points {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }
    .flight-point {
        font-size: 18px;
        font-weight: bold;
    }
    .flight-arrow {
        margin: 0 15px;
        color: #007bff;
        flex-grow: 1;
        text-align: center;
        position: relative;
    }
    .flight-arrow::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 2px;
        background-color: #007bff;
        z-index: 1;
    }
    .flight-arrow i {
        background-color: #f9f9f9;
        padding: 0 10px;
        position: relative;
        z-index: 2;
    }
    .flight-times {
        display: flex;
        justify-content: space-between;
        color: #666;
        font-size: 14px;
    }
    .price-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    .price-table th {
        background-color: #f2f2f2;
        text-align: left;
        padding: 10px;
        font-weight: normal;
        color: #666;
    }
    .price-table td {
        padding: 10px;
        border-top: 1px solid #eee;
        transition: background-color 0.3s ease;
    }
    .price-table tr:hover td {
        background-color: #f1f5f9;
    }
    .price-table .total-row td {
        border-top: 2px solid #ddd;
        font-weight: bold;
        font-size: 18px;
    }
    .actions {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }
    .btn {
        padding: 12px 25px;
        border-radius: 6px;
        font-weight: bold;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        text-align: center;
        border: none;
        font-size: 16px;
        color: white;
    }
    .btn-primary {
        background: linear-gradient(90deg, #007bff, #0056b3);
    }
    .btn-secondary {
        background-color: #6c757d;
    }
    .btn-success {
        background-color: #28a745;
    }
    .btn:hover {
        transform: scale(1.05);
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }
    .success-message {
        background-color: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
        animation: fadeIn 0.5s ease-out;
    }
    @media print {
        .no-print { display: none; }
        body { background: none; }
        body::before { display: none; }
        .invoice-container { box-shadow: none; max-width: 100%; margin: 0; }
        .actions { display: none; }
    }
    @media (max-width: 768px) {
        .info-grid { grid-template-columns: 1fr; }
        .flight-details { flex-direction: column; text-align: center; }
        .airline-logo { margin-right: 0; margin-bottom: 15px; }
        .invoice-container { margin: 15px; }
        .flight-points { flex-direction: column; }
        .flight-arrow { margin: 15px 0; }
    }
    </style>
</head>
<body>
    <header class="header no-print">
        <div class="logo">SEVEN AIRLINE</div>
        <ul class="menu">
            <li><a href="account.php"><i class="fas fa-user-circle"></i> Tài khoản</a></li>
            <li><a href="booking.php"><i class="fas fa-ticket-alt"></i> Đặt vé</a></li>
            <li><a href="history.php"><i class="fas fa-history"></i> Lịch sử vé</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
        </ul>
        <div class="user-info">👤 <?= htmlspecialchars($username) ?></div>
    </header>

    <div class="invoice-container">
        <div class="invoice-header">
            <h1><i class="fas fa-receipt"></i> Hóa Đơn Đặt Vé</h1>
            <div class="booking-ref">Mã đặt vé: <?= $booking_reference ?></div>
        </div>

        <div class="invoice-body">
            <?php if ($payment_confirmed): ?>
            <div class="success-message">
                Thanh toán thành công! Cảm ơn quý khách đã sử dụng dịch vụ của SEVEN AIRLINE.
            </div>
            <?php endif; ?>

            <!-- Thông tin chuyến bay -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-plane"></i> Thông Tin Chuyến Bay
                </div>
                <!-- Chuyến đi -->
                <div class="flight-details">
                    <img src="<?= $logo_path ?>" alt="Logo hãng bay" class="airline-logo">
                    <div class="flight-route">
                        <div class="flight-points">
                            <div class="flight-point"><?= $departure_name ?></div>
                            <div class="flight-arrow"><i class="fas fa-plane"></i></div>
                            <div class="flight-point"><?= $destination_name ?></div>
                        </div>
                        <div class="flight-times">
                            <span>Khởi hành: <?= $flight['flight_start_time'] ?> - <?= date('d/m/Y', strtotime($departure_date)) ?></span>
                            <span>Đến nơi: <?= $flight['flight_end_time'] ?> - <?= date('d/m/Y', strtotime($departure_date)) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Chuyến về (nếu là vé khứ hồi) -->
                <?php if ($ticket_type === 'round_trip'): ?>
                <div class="flight-details">
                    <img src="<?= $logo_path ?>" alt="Logo hãng bay" class="airline-logo">
                    <div class="flight-route">
                        <div class="flight-points">
                            <div class="flight-point"><?= $destination_name ?></div>
                            <div class="flight-arrow"><i class="fas fa-plane"></i></div>
                            <div class="flight-point"><?= $departure_name ?></div>
                        </div>
                        <div class="flight-times">
                            <span>Khởi hành: <?= $flight['flight_start_time'] ?> - <?= date('d/m/Y', strtotime($return_date)) ?></span>
                            <span>Đến nơi: <?= $flight['flight_end_time'] ?> - <?= date('d/m/Y', strtotime($return_date)) ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Hãng hàng không</span>
                        <span class="info-value"><?= htmlspecialchars($airline['airline_name']) ?> (<?= htmlspecialchars($airline['airline_code']) ?>)</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Loại vé</span>
                        <span class="info-value"><?= $ticket_type === 'round_trip' ? 'Khứ hồi' : 'Một chiều' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ngày đi</span>
                        <span class="info-value"><?= date('d/m/Y', strtotime($departure_date)) ?></span>
                    </div>
                    <?php if ($ticket_type === 'round_trip'): ?>
                    <div class="info-item">
                        <span class="info-label">Ngày về</span>
                        <span class="info-value"><?= date('d/m/Y', strtotime($return_date)) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Thông tin hành khách -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-users"></i> Thông Tin Hành Khách
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Người lớn</span>
                        <span class="info-value"><?= $adults ?> người</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Trẻ em</span>
                        <span class="info-value"><?= $children ?> người</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Em bé</span>
                        <span class="info-value"><?= $infants ?> người</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tổng số hành khách</span>
                        <span class="info-value"><?= $total_passengers ?> người</span>
                    </div>
                </div>
            </div>

            <!-- Chi tiết thanh toán -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-money-bill-wave"></i> Chi Tiết Thanh Toán
                </div>
                <table class="price-table">
                    <tr>
                        <th>Mô tả</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                    </tr>
                    <tr>
                        <td>Vé người lớn</td>
                        <td><?= $adults ?></td>
                        <td><?= number_format($base_price, 0, ',', '.') ?> đ</td>
                        <td><?= number_format($adult_price, 0, ',', '.') ?> đ</td>
                    </tr>
                    <?php if ($children > 0): ?>
                    <tr>
                        <td>Vé trẻ em (giảm 25%)</td>
                        <td><?= $children ?></td>
                        <td><?= number_format($base_price * 0.75, 0, ',', '.') ?> đ</td>
                        <td><?= number_format($child_price, 0, ',', '.') ?> đ</td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($infants > 0): ?>
                    <tr>
                        <td>Vé em bé (10% giá vé)</td>
                        <td><?= $infants ?></td>
                        <td><?= number_format($base_price * 0.1, 0, ',', '.') ?> đ</td>
                        <td><?= number_format($infant_price, 0, ',', '.') ?> đ</td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($ticket_type === 'round_trip'): ?>
                    <tr>
                        <td colspan="3">Phụ thu vé khứ hồi (x2)</td>
                        <td><?= number_format($adult_price + $child_price + $infant_price, 0, ',', '.') ?> đ</td>
                    </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <td colspan="3">Tổng tiền thanh toán</td>
                        <td><?= number_format($total_price, 0, ',', '.') ?> đ</td>
                    </tr>
                </table>
            </div>

            <!-- Điều khoản và các chú ý -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-info-circle"></i> Điều Khoản & Lưu Ý
                </div>
                <ul style="padding-left: 20px; color: #666;">
                    <li>Vui lòng có mặt tại sân bay trước giờ khởi hành ít nhất 2 giờ đối với chuyến bay quốc tế và 1 giờ đối với chuyến bay nội địa.</li>
                    <li>Hành khách cần mang theo giấy tờ tùy thân hợp lệ khi làm thủ tục.</li>
                    <li>Hoàn/đổi vé: Vui liên hệ tổng đài SEVEN AIRLINE theo số 1900-xxxx để được hỗ trợ.</li>
                    <li>Hành lý: Mỗi khách được phép mang theo 7kg hành lý xách tay và 23kg hành lý ký gửi.</li>
                </ul>
            </div>

            <!-- Nút thao tác -->
            <div class="actions">
                <?php if (!$payment_confirmed): ?>
                <a href="search_flight.php" class="btn btn-secondary no-print"><i class="fas fa-arrow-left"></i> Quay lại</a>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="confirm_booking" class="btn btn-success no-print"><i class="fas fa-check"></i> Xác nhận đặt vé</button>
                </form>
                <?php else: ?>
                <a href="user_page.php" class="btn btn-secondary no-print"><i class="fas fa-home"></i> Về trang chính</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>