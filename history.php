<?php
session_start();
require 'app/config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['customer_username'])) {
    header('Location: customer_auth.php');
    exit;
}

$username = $_SESSION['customer_username'];

// Xử lý hủy vé
if (isset($_POST['cancel_booking'])) {
    $ma_hoadon = $_POST['ma_hoadon'];
    $stmt = $pdo->prepare("DELETE FROM hoadon WHERE ma_hoadon = ? AND name = ?");
    $stmt->execute([$ma_hoadon, $username]);
    header("Location: history.php");
    exit;
}

// Truy xuất dữ liệu từ bảng hoadon
$stmt = $pdo->prepare("SELECT * FROM hoadon WHERE name = ? ORDER BY created_at DESC");
$stmt->execute([$username]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ánh xạ mã sân bay sang tên đầy đủ
$airport_names = [
    'SGN' => 'Hồ Chí Minh (SGN)', 'HAN' => 'Hà Nội (HAN)', 'DAD' => 'Đà Nẵng (DAD)', 'CXR' => 'Nha Trang (CXR)',
    'PQC' => 'Phú Quốc (PQC)', 'HPH' => 'Hải Phòng (HPH)', 'VCA' => 'Cần Thơ (VCA)', 'DLI' => 'Đà Lạt (DLI)',
    'BKK' => 'Bangkok (BKK)', 'KUL' => 'Kuala Lumpur (KUL)', 'SIN' => 'Singapore (SIN)', 'MNL' => 'Manila (MNL)',
    'CGK' => 'Jakarta (CGK)', 'PNH' => 'Phnom Penh (PNH)', 'RGN' => 'Yangon (RGN)', 'VTE' => 'Vientiane (VTE)',
    'NRT' => 'Tokyo Narita (NRT)', 'ICN' => 'Seoul Incheon (ICN)', 'HKG' => 'Hong Kong (HKG)', 'PEK' => 'Bắc Kinh (PEK)',
    'JFK' => 'New York JFK (JFK)', 'LAX' => 'Los Angeles (LAX)', 'YYZ' => 'Toronto Pearson (YYZ)',
    'LHR' => 'London Heathrow (LHR)', 'CDG' => 'Paris Charles de Gaulle (CDG)', 'FRA' => 'Frankfurt (FRA)',
    'AMS' => 'Amsterdam (AMS)', 'MAD' => 'Madrid (MAD)', 'FCO' => 'Rome Fiumicino (FCO)', 'BER' => 'Berlin Brandenburg (BER)',
    'VIE' => 'Vienna (VIE)', 'ZRH' => 'Zurich (ZRH)',
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Đặt Vé - SEVEN AIRLINE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: url('assets/images/5d7e1ce1baca7839954d4b278a87cb74.jpg') no-repeat center center fixed;
        background-size: cover;
        color: #333;
        overflow-x: hidden; /* Thêm để tránh tràn ngang */
    }

    body::before {
        content: "";
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background-color: rgba(255, 255, 255, 0.5);
        z-index: -1;
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
        animation: fadeInDown 0.8s ease-out; /* Thêm hiệu ứng từ booking */
    }

    @keyframes fadeInDown { /* Hiệu ứng từ booking */
        0% { opacity: 0; transform: translateY(-20px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    .logo {
        font-size: 24px;
        font-weight: bold;
        color: #007bff;
        animation: bounceIn 1s ease-out; /* Thêm hiệu ứng từ booking */
    }

    @keyframes bounceIn { /* Hiệu ứng từ booking */
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
        position: relative; /* Thêm để hỗ trợ hiệu ứng underline */
    }

    .menu li a {
        text-decoration: none;
        color: #333;
        font-weight: bold;
        transition: color 0.3s ease, transform 0.3s ease; /* Kết hợp hiệu ứng gốc và mới */
        display: inline-block; /* Thêm từ booking */
    }

    .menu li a:hover {
        color: #007bff;
        transform: scale(1.05); /* Thêm hiệu ứng scale từ booking */
    }

    .menu li a::after { /* Thêm hiệu ứng underline từ booking */
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

    .user-info {
        font-size: 16px;
        color: #333;
        animation: fadeIn 1s ease-out; /* Thêm hiệu ứng từ booking */
    }

    @keyframes fadeIn { /* Hiệu ứng từ booking */
        0% { opacity: 0; }
        100% { opacity: 1; }
    }

    .container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 20px;
        background-color: rgba(255, 255, 255, 0.9);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        animation: fadeInUp 0.8s ease-out; /* Thêm hiệu ứng từ booking */
    }

    @keyframes fadeInUp { /* Hiệu ứng từ booking */
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    .header-section {
        background: linear-gradient(90deg, #007bff, #0056b3);
        color: white;
        padding: 20px;
        text-align: center;
        border-radius: 12px 12px 0 0;
        margin-bottom: 20px;
        animation: fadeIn 0.5s ease-out; /* Thêm hiệu ứng từ booking */
    }

    .header-section h1 {
        margin: 0;
        font-size: 28px;
    }

    .header-section h1 i {
        margin-right: 10px;
    }

    .booking-list {
        display: grid;
        gap: 20px;
    }

    .booking-card {
        background-color: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease; /* Giữ hiệu ứng gốc */
        opacity: 0;
        animation: slideIn 0.5s ease-out forwards; /* Thêm hiệu ứng từ booking */
    }

    .booking-card:nth-child(1) { animation-delay: 0.1s; }
    .booking-card:nth-child(2) { animation-delay: 0.2s; }
    .booking-card:nth-child(3) { animation-delay: 0.3s; }
    .booking-card:nth-child(4) { animation-delay: 0.4s; }

    @keyframes slideIn { /* Hiệu ứng từ booking */
        0% { opacity: 0; transform: translateX(-20px); }
        100% { opacity: 1; transform: translateX(0); }
    }

    .booking-card:hover {
        transform: translateY(-5px); /* Giữ hiệu ứng gốc */
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1); /* Giữ hiệu ứng gốc */
    }

    .booking-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }

    .booking-header .id {
        font-weight: bold;
        font-size: 18px;
        color: #007bff;
    }

    .booking-header .date {
        font-size: 14px;
        color: #666;
    }

    .booking-details {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
    }

    .detail-label {
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
    }

    .detail-value {
        font-size: 16px;
        font-weight: bold;
    }

    .flight-info {
        margin-top: 15px;
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 8px;
        transition: transform 0.3s ease; /* Thêm hiệu ứng từ booking */
    }

    .flight-info:hover {
        transform: scale(1.02); /* Thêm hiệu ứng từ booking */
    }

    .flight-route {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .flight-point {
        font-size: 16px;
        font-weight: bold;
    }

    .flight-arrow {
        color: #007bff;
        margin: 0 15px;
        position: relative;
        flex-grow: 1;
        text-align: center;
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
        font-size: 14px;
        color: #666;
        text-align: center;
    }

    .actions {
        margin-top: 15px;
        display: flex;
        gap: 10px;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: bold;
        text-decoration: none;
        transition: all 0.3s ease; /* Kết hợp hiệu ứng gốc và mới */
        cursor: pointer;
        border: none;
        font-size: 14px;
        color: white;
    }

    .btn-details {
        background: linear-gradient(90deg, #007bff, #0056b3); /* Cập nhật từ booking */
    }

    .btn-cancel {
        background-color: #dc3545;
    }

    .btn:hover {
        opacity: 0.9; /* Giữ hiệu ứng gốc */
        transform: translateY(-2px) scale(1.05); /* Kết hợp hiệu ứng gốc và scale từ booking */
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.2); /* Thêm bóng đổ từ booking */
    }

    .no-bookings {
        text-align: center;
        font-size: 18px;
        color: #666;
        padding: 40px 0;
        animation: fadeIn 0.5s ease-out; /* Thêm hiệu ứng từ booking */
    }

    .back-btn {
        display: inline-block;
        margin-top: 20px;
        padding: 12px 25px;
        background: linear-gradient(90deg, #007bff, #0056b3); /* Cập nhật từ booking */
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: bold;
        transition: all 0.3s ease; /* Kết hợp hiệu ứng gốc và mới */
    }

    .back-btn:hover {
        opacity: 0.9; /* Giữ hiệu ứng gốc */
        transform: translateY(-2px) scale(1.05); /* Kết hợp hiệu ứng gốc và scale từ booking */
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); /* Thêm bóng đổ từ booking */
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        max-width: 600px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        position: relative;
        animation: fadeInUp 0.5s ease-out; /* Thêm hiệu ứng từ booking */
    }

    .modal-header {
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 22px;
        color: #007bff;
    }

    .close-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 24px;
        color: #666;
        cursor: pointer;
        border: none;
        background: none;
        transition: color 0.3s ease; /* Giữ hiệu ứng gốc */
    }

    .close-btn:hover {
        color: #333; /* Giữ hiệu ứng gốc */
    }

    @media (max-width: 768px) {
        .header {
            flex-direction: column;
            text-align: center;
        }

        .menu {
            flex-direction: column;
            margin: 10px 0;
        }

        .menu li {
            margin: 5px 0;
        }

        .booking-details {
            grid-template-columns: 1fr;
        }

        .flight-route {
            flex-direction: column;
            text-align: center;
        }

        .flight-arrow {
            margin: 10px 0;
        }

        .actions {
            flex-direction: column;
            gap: 5px;
        }
    }
    </style>
</head>
<body>
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

    <div class="container">
        <div class="header-section">
            <h1><i class="fas fa-history"></i> Lịch Sử Đặt Vé</h1>
        </div>

        <div class="booking-list">
            <?php if (empty($bookings)): ?>
                <div class="no-bookings">
                    Chưa có lịch sử đặt vé nào!
                </div>
            <?php else: ?>
                <?php foreach ($bookings as $booking): 
                    $departure_name = $airport_names[$booking['departure']] ?? $booking['departure'];
                    $destination_name = $airport_names[$booking['destination']] ?? $booking['destination'];
                    $total_price = $booking['ticket_price']; // Lấy trực tiếp từ ticket_price, không cần tính lại
                ?>
                    <div class="booking-card">
                        <div class="booking-header">
                            <span class="id">Mã hóa đơn: #<?= $booking['ma_hoadon'] ?></span>
                            <span class="date">Ngày đặt: <?= date('d/m/Y H:i', strtotime($booking['created_at'])) ?></span>
                        </div>
                        <div class="booking-details">
                            <div class="detail-item">
                                <span class="detail-label">Hãng hàng không</span>
                                <span class="detail-value"><?= htmlspecialchars($booking['airline_name']) ?> (<?= htmlspecialchars($booking['airline_code']) ?>)</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Loại vé</span>
                                <span class="detail-value"><?= $booking['ticket_type'] === 'round_trip' ? 'Khứ hồi' : 'Một chiều' ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Ngày đi</span>
                                <span class="detail-value"><?= date('d/m/Y', strtotime($booking['departure_date'])) ?></span>
                            </div>
                            <?php if ($booking['ticket_type'] === 'round_trip' && $booking['return_date']): ?>
                            <div class="detail-item">
                                <span class="detail-label">Ngày về</span>
                                <span class="detail-value"><?= date('d/m/Y', strtotime($booking['return_date'])) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="detail-item">
                                <span class="detail-label">Hành khách</span>
                                <span class="detail-value"><?= $booking['adults'] ?> người lớn, <?= $booking['children'] ?> trẻ em, <?= $booking['infants'] ?> em bé</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Tổng tiền</span>
                                <span class="detail-value"><?= number_format($total_price, 0, ',', '.') ?> đ</span>
                            </div>
                        </div>
                        <div class="flight-info">
                            <div class="flight-route">
                                <span class="flight-point"><?= $departure_name ?></span>
                                <span class="flight-arrow"><i class="fas fa-plane"></i></span>
                                <span class="flight-point"><?= $destination_name ?></span>
                            </div>
                            <div class="flight-times">
                                <?= $booking['flight_start_time'] ?> - <?= $booking['flight_end_time'] ?>
                            </div>
                            <?php if ($booking['ticket_type'] === 'round_trip' && $booking['return_date']): ?>
                            <div class="flight-route" style="margin-top: 10px;">
                                <span class="flight-point"><?= $destination_name ?></span>
                                <span class="flight-arrow"><i class="fas fa-plane"></i></span>
                                <span class="flight-point"><?= $departure_name ?></span>
                            </div>
                            <div class="flight-times">
                                <?= $booking['flight_start_time'] ?> - <?= $booking['flight_end_time'] ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="actions">
                            <button class="btn btn-details" onclick="showDetails('<?= $booking['ma_hoadon'] ?>')">Xem chi tiết</button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn hủy vé này không?');">
                                <input type="hidden" name="ma_hoadon" value="<?= $booking['ma_hoadon'] ?>">
                                <button type="submit" name="cancel_booking" class="btn btn-cancel">Hủy vé</button>
                            </form>
                        </div>
                    </div>

                    <div id="modal-<?= $booking['ma_hoadon'] ?>" class="modal">
                        <div class="modal-content">
                            <button class="close-btn" onclick="closeModal('<?= $booking['ma_hoadon'] ?>')">×</button>
                            <div class="modal-header">
                                <h2>Chi tiết hóa đơn #<?= $booking['ma_hoadon'] ?></h2>
                            </div>
                            <div class="booking-details">
                                <div class="detail-item">
                                    <span class="detail-label">Hãng hàng không</span>
                                    <span class="detail-value"><?= htmlspecialchars($booking['airline_name']) ?> (<?= htmlspecialchars($booking['airline_code']) ?>)</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Quốc gia</span>
                                    <span class="detail-value"><?= htmlspecialchars($booking['country']) ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Loại vé</span>
                                    <span class="detail-value"><?= $booking['ticket_type'] === 'round_trip' ? 'Khứ hồi' : 'Một chiều' ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Ngày đặt</span>
                                    <span class="detail-value"><?= date('d/m/Y H:i', strtotime($booking['created_at'])) ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Ngày đi</span>
                                    <span class="detail-value"><?= date('d/m/Y', strtotime($booking['departure_date'])) ?></span>
                                </div>
                                <?php if ($booking['ticket_type'] === 'round_trip' && $booking['return_date']): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Ngày về</span>
                                    <span class="detail-value"><?= date('d/m/Y', strtotime($booking['return_date'])) ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="detail-item">
                                    <span class="detail-label">Hành khách</span>
                                    <span class="detail-value"><?= $booking['adults'] ?> người lớn, <?= $booking['children'] ?> trẻ em, <?= $booking['infants'] ?> em bé</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Tổng tiền</span>
                                    <span class="detail-value"><?= number_format($total_price, 0, ',', '.') ?> đ</span>
                                </div>
                            </div>
                            <div class="flight-info">
                                <div class="flight-route">
                                    <span class="flight-point"><?= $departure_name ?></span>
                                    <span class="flight-arrow"><i class="fas fa-plane"></i></span>
                                    <span class="flight-point"><?= $destination_name ?></span>
                                </div>
                                <div class="flight-times">
                                    <?= $booking['flight_start_time'] ?> - <?= $booking['flight_end_time'] ?>
                                </div>
                                <?php if ($booking['ticket_type'] === 'round_trip' && $booking['return_date']): ?>
                                <div class="flight-route" style="margin-top: 10px;">
                                    <span class="flight-point"><?= $destination_name ?></span>
                                    <span class="flight-arrow"><i class="fas fa-plane"></i></span>
                                    <span class="flight-point"><?= $departure_name ?></span>
                                </div>
                                <div class="flight-times">
                                    <?= $booking['flight_start_time'] ?> - <?= $booking['flight_end_time'] ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <a href="user_page.php" class="back-btn"><i class="fas fa-arrow-left"></i> Quay lại trang chính</a>
    </div>

    <script>
        function showDetails(ma_hoadon) {
            document.getElementById('modal-' + ma_hoadon).style.display = 'flex';
        }

        function closeModal(ma_hoadon) {
            document.getElementById('modal-' + ma_hoadon).style.display = 'none';
        }

        window.onclick = function(event) {
            const modals = document.getElementsByClassName('modal');
            for (let i = 0; i < modals.length; i++) {
                if (event.target === modals[i]) {
                    modals[i].style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>