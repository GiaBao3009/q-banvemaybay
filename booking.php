<?php
session_start();
require 'app/config/database.php'; // Kết nối database

// Kiểm tra đăng nhập
if (!isset($_SESSION['customer_username'])) {
    header('Location: customer_auth.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $departure_date = $_POST['departure_date'];
    $return_date = $_POST['return_date'] ?? NULL; // NULL nếu không có
    $ticket_type = $_POST['ticket_type'];
    $adults = (int)$_POST['adults'];
    $children = (int)$_POST['children'];
    $infants = (int)$_POST['infants'];
    $departure = $_POST['departure'];
    $destination = $_POST['destination'];

    // Lưu vào bảng bookings
    $stmt = $pdo->prepare("
        INSERT INTO bookings (name, ticket_type, departure, destination, departure_date, return_date, adults, children, infants)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$username, $ticket_type, $departure, $destination, $departure_date, $return_date, $adults, $children, $infants]);

    // Lưu vào session
    $_SESSION['departure_date'] = $departure_date;
    $_SESSION['return_date'] = $return_date;
    $_SESSION['ticket_type'] = $ticket_type;
    $_SESSION['adults'] = $adults;
    $_SESSION['children'] = $children;
    $_SESSION['infants'] = $infants;
    $_SESSION['departure'] = $departure;
    $_SESSION['destination'] = $destination;

    header('Location: search_flight.php');
    exit;
}

$username = $_SESSION['customer_username'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt vé máy bay - SEVEN AIRLINE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: url('assets/images/5d7e1ce1baca7839954d4b278a87cb74.jpg') no-repeat center center fixed;
        background-size: cover;
        color: #333;
        overflow-x: hidden; /* Ngăn tràn ngang */
    }
    body::before {
        content: "";
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.5);
        z-index: 0;
    }

    /* Header */
    .header {
        background-color: rgba(255, 255, 255, 0.85);
        padding: 10px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
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
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .menu li {
        margin: 0 15px;
        position: relative;
    }

    .menu li a {
        text-decoration: none;
        color: #333;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .menu li a:hover {
        color: #007bff;
        transform: scale(1.05); /* Phóng to nhẹ khi hover */
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
        width: 100%; /* Hiệu ứng underline khi hover */
    }

    .user-info {
        font-size: 16px;
        animation: fadeIn 1s ease-out;
    }

    @keyframes fadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }

    /* Content */
    .content {
        max-width: 800px;
        margin: 20px auto;
        padding: 40px;
        background-color: rgba(255, 255, 255, 0.85);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        position: relative;
        z-index: 1;
        animation: fadeInUp 0.8s ease-out;
    }

    @keyframes fadeInUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    .content header {
        margin-bottom: 30px;
        text-align: center;
    }

    .content header h1 {
        margin: 0;
        font-size: 28px;
        color: #007bff;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .content header h1 i {
        margin-right: 10px;
        animation: fly 2s infinite alternate ease-in-out;
    }

    @keyframes fly {
        0% { transform: translateY(0); }
        100% { transform: translateY(-5px); }
    }

    /* Booking Form */
    .booking-form .form-group {
        margin-bottom: 18px;
        position: relative;
        opacity: 0;
        animation: slideIn 0.5s ease-out forwards;
    }

    @keyframes slideIn {
        0% { opacity: 0; transform: translateX(-20px); }
        100% { opacity: 1; transform: translateX(0); }
    }

    .booking-form .form-group:nth-child(1) { animation-delay: 0.1s; }
    .booking-form .form-group:nth-child(2) { animation-delay: 0.2s; }
    .booking-form .form-group:nth-child(3) { animation-delay: 0.3s; }
    .booking-form .form-group:nth-child(4) { animation-delay: 0.4s; }
    .booking-form .form-group:nth-child(5) { animation-delay: 0.5s; }

    .booking-form .form-group label {
        font-weight: bold;
        color: #333;
        display: block;
        margin-bottom: 5px;
        transition: color 0.3s ease;
    }

    .ticket-type {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 15px;
    }

    .ticket-type label {
        flex: 1;
        text-align: center;
        padding: 12px;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
        border: 2px solid #ccc;
        color: #333;
    }

    .ticket-type input {
        display: none;
    }

    .ticket-type input:checked + label {
        background: linear-gradient(90deg, #007bff, #0056b3);
        color: white;
        border-color: #007bff;
        box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
        transform: scale(1.05); /* Phóng to nhẹ khi chọn */
    }

    .ticket-type label:hover {
        background-color: #e9ecef;
        border-color: #007bff;
        transform: scale(1.02); /* Hiệu ứng hover nhẹ */
    }

    .booking-form .form-group select,
    .booking-form .form-group input {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #ccc;
        transition: all 0.3s ease;
        font-size: 16px;
    }

    .booking-form .form-group input:focus,
    .booking-form .form-group select:focus {
        border-color: #007bff;
        outline: none;
        box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
        transform: scale(1.02); /* Phóng to nhẹ khi focus */
    }

    #return_label, #return_date {
        transition: all 0.3s ease;
    }

    #return_date[style*="display: inline-block"] {
        animation: slideInRight 0.5s ease-out;
    }

    @keyframes slideInRight {
        0% { opacity: 0; transform: translateX(20px); }
        100% { opacity: 1; transform: translateX(0); }
    }

    .passengers {
        display: flex;
        gap: 15px;
    }

    .passengers div {
        flex: 1;
    }

    .btn-submit {
        width: 100%;
        background: linear-gradient(90deg, #007bff, #0056b3);
        color: #fff;
        padding: 14px;
        border: none;
        border-radius: 8px;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        background: linear-gradient(90deg, #0056b3, #003f7f);
        transform: scale(1.05); /* Phóng to khi hover */
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
    }

    .btn-back {
        display: inline-block;
        margin-top: 20px;
        text-decoration: none;
        padding: 12px 20px;
        border-radius: 6px;
        background-color: #3498db;
        color: #fff;
        transition: all 0.3s ease;
    }

    .btn-back:hover {
        background-color: #2980b9;
        transform: scale(1.05); /* Phóng to khi hover */
        box-shadow: 0 0 8px rgba(52, 152, 219, 0.5);
    }

    .message {
        margin-bottom: 20px;
        padding: 12px;
        border-radius: 6px;
        animation: fadeIn 0.5s ease-out;
    }

    .success {
        background-color: #d4edda;
        color: #155724;
    }

    .error {
        background-color: #f8d7da;
        color: #721c24;
    }

    /* Responsive */
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
        .content {
            padding: 20px;
        }
        .content header h1 {
            font-size: 22px;
        }
        .passengers {
            flex-direction: column;
            gap: 10px;
        }
    }body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: url('assets/images/5d7e1ce1baca7839954d4b278a87cb74.jpg') no-repeat center center fixed;
        background-size: cover;
        color: #333;
        overflow-x: hidden; /* Ngăn tràn ngang */
    }
    body::before {
        content: "";
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.5);
        z-index: 0;
    }

    /* Header */
    .header {
        background-color: rgba(255, 255, 255, 0.85);
        padding: 10px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
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
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .menu li {
        margin: 0 15px;
        position: relative;
    }

    .menu li a {
        text-decoration: none;
        color: #333;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .menu li a:hover {
        color: #007bff;
        transform: scale(1.05); /* Phóng to nhẹ khi hover */
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
        width: 100%; /* Hiệu ứng underline khi hover */
    }

    .user-info {
        font-size: 16px;
        animation: fadeIn 1s ease-out;
    }

    @keyframes fadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }

    /* Content */
    .content {
        max-width: 800px;
        margin: 20px auto;
        padding: 40px;
        background-color: rgba(255, 255, 255, 0.85);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        position: relative;
        z-index: 1;
        animation: fadeInUp 0.8s ease-out;
    }

    @keyframes fadeInUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    .content header {
        margin-bottom: 30px;
        text-align: center;
    }

    .content header h1 {
        margin: 0;
        font-size: 28px;
        color: #007bff;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .content header h1 i {
        margin-right: 10px;
        animation: fly 2s infinite alternate ease-in-out;
    }

    @keyframes fly {
        0% { transform: translateY(0); }
        100% { transform: translateY(-5px); }
    }

    /* Booking Form */
    .booking-form .form-group {
        margin-bottom: 18px;
        position: relative;
        opacity: 0;
        animation: slideIn 0.5s ease-out forwards;
    }

    @keyframes slideIn {
        0% { opacity: 0; transform: translateX(-20px); }
        100% { opacity: 1; transform: translateX(0); }
    }

    .booking-form .form-group:nth-child(1) { animation-delay: 0.1s; }
    .booking-form .form-group:nth-child(2) { animation-delay: 0.2s; }
    .booking-form .form-group:nth-child(3) { animation-delay: 0.3s; }
    .booking-form .form-group:nth-child(4) { animation-delay: 0.4s; }
    .booking-form .form-group:nth-child(5) { animation-delay: 0.5s; }

    .booking-form .form-group label {
        font-weight: bold;
        color: #333;
        display: block;
        margin-bottom: 5px;
        transition: color 0.3s ease;
    }

    .ticket-type {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 15px;
    }

    .ticket-type label {
        flex: 1;
        text-align: center;
        padding: 12px;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
        border: 2px solid #ccc;
        color: #333;
    }

    .ticket-type input {
        display: none;
    }

    .ticket-type input:checked + label {
        background: linear-gradient(90deg, #007bff, #0056b3);
        color: white;
        border-color: #007bff;
        box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
        transform: scale(1.05); /* Phóng to nhẹ khi chọn */
    }

    .ticket-type label:hover {
        background-color: #e9ecef;
        border-color: #007bff;
        transform: scale(1.02); /* Hiệu ứng hover nhẹ */
    }

    .booking-form .form-group select,
    .booking-form .form-group input {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #ccc;
        transition: all 0.3s ease;
        font-size: 16px;
    }

    .booking-form .form-group input:focus,
    .booking-form .form-group select:focus {
        border-color: #007bff;
        outline: none;
        box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
        transform: scale(1.02); /* Phóng to nhẹ khi focus */
    }

    #return_label, #return_date {
        transition: all 0.3s ease;
    }

    #return_date[style*="display: inline-block"] {
        animation: slideInRight 0.5s ease-out;
    }

    @keyframes slideInRight {
        0% { opacity: 0; transform: translateX(20px); }
        100% { opacity: 1; transform: translateX(0); }
    }

    .passengers {
        display: flex;
        gap: 15px;
    }

    .passengers div {
        flex: 1;
    }

    .btn-submit {
        width: 100%;
        background: linear-gradient(90deg, #007bff, #0056b3);
        color: #fff;
        padding: 14px;
        border: none;
        border-radius: 8px;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        background: linear-gradient(90deg, #0056b3, #003f7f);
        transform: scale(1.05); /* Phóng to khi hover */
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
    }

    .btn-back {
        display: inline-block;
        margin-top: 20px;
        text-decoration: none;
        padding: 12px 20px;
        border-radius: 6px;
        background-color: #3498db;
        color: #fff;
        transition: all 0.3s ease;
    }

    .btn-back:hover {
        background-color: #2980b9;
        transform: scale(1.05); /* Phóng to khi hover */
        box-shadow: 0 0 8px rgba(52, 152, 219, 0.5);
    }

    .message {
        margin-bottom: 20px;
        padding: 12px;
        border-radius: 6px;
        animation: fadeIn 0.5s ease-out;
    }

    .success {
        background-color: #d4edda;
        color: #155724;
    }

    .error {
        background-color: #f8d7da;
        color: #721c24;
    }

    /* Responsive */
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
        .content {
            padding: 20px;
        }
        .content header h1 {
            font-size: 22px;
        }
        .passengers {
            flex-direction: column;
            gap: 10px;
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

    <div class="content">
        <header>
            <h1><i class="fas fa-ticket-alt"></i> Đặt vé máy bay</h1>
        </header>

        <div class="account-container">
            <form action="process_booking.php" method="POST" class="booking-form">
                <!-- Loại vé -->
                <div class="form-group">
                    <label>Loại vé:</label>
                    <div class="ticket-type">
                        <label>
                            <input type="radio" name="ticket_type" value="one_way" checked onclick="toggleReturnDate(false)">
                            Một chiều
                        </label>
                        <label>
                            <input type="radio" name="ticket_type" value="round_trip" onclick="toggleReturnDate(true)">
                            Khứ hồi
                        </label>
                    </div>
                </div>

                <!-- Điểm đi -->
                <div class="form-group">
                    <label>Điểm đi:</label>
                    <select name="departure" required>
                        <optgroup label="VIỆT NAM">
                            <option value="SGN">Hồ Chí Minh (SGN)</option>
                            <option value="HAN">Hà Nội (HAN)</option>
                            <option value="DAD">Đà Nẵng (DAD)</option>
                            <option value="CXR">Nha Trang (CXR)</option>
                            <option value="PQC">Phú Quốc (PQC)</option>
                            <option value="HPH">Hải Phòng (HPH)</option>
                            <option value="VCA">Cần Thơ (VCA)</option>
                            <option value="DLI">Đà Lạt (DLI)</option>
                        </optgroup>
                        <optgroup label="ĐÔNG NAM Á">
                            <option value="BKK">Bangkok (BKK)</option>
                            <option value="KUL">Kuala Lumpur (KUL)</option>
                            <option value="SIN">Singapore (SIN)</option>
                            <option value="MNL">Manila (MNL)</option>
                            <option value="CGK">Jakarta (CGK)</option>
                            <option value="PNH">Phnom Penh (PNH)</option>
                            <option value="RGN">Yangon (RGN)</option>
                            <option value="VTE">Vientiane (VTE)</option>
                        </optgroup>
                        <optgroup label="ĐÔNG Á">
                            <option value="NRT">Tokyo Narita (NRT)</option>
                            <option value="ICN">Seoul Incheon (ICN)</option>
                            <option value="HKG">Hong Kong (HKG)</option>
                            <option value="PEK">Bắc Kinh (PEK)</option>
                        </optgroup>
                        <optgroup label="BẮC MỸ">
                            <option value="JFK">New York JFK (JFK)</option>
                            <option value="LAX">Los Angeles (LAX)</option>
                            <option value="YYZ">Toronto Pearson (YYZ)</option>
                        </optgroup>
                        <optgroup label="CHÂU ÂU">
                            <option value="LHR">London Heathrow (LHR)</option>
                            <option value="CDG">Paris Charles de Gaulle (CDG)</option>
                            <option value="FRA">Frankfurt (FRA)</option>
                            <option value="AMS">Amsterdam (AMS)</option>
                            <option value="MAD">Madrid (MAD)</option>
                            <option value="FCO">Rome Fiumicino (FCO)</option>
                            <option value="BER">Berlin Brandenburg (BER)</option>
                            <option value="VIE">Vienna (VIE)</option>
                            <option value="ZRH">Zurich (ZRH)</option>
                        </optgroup>
                    </select>
                </div>

                <!-- Điểm đến -->
                <div class="form-group">
                    <label>Điểm đến:</label>
                    <select name="destination" required>
                        <optgroup label="VIỆT NAM">
                            <option value="SGN">Hồ Chí Minh (SGN)</option>
                            <option value="HAN">Hà Nội (HAN)</option>
                            <option value="DAD">Đà Nẵng (DAD)</option>
                            <option value="CXR">Nha Trang (CXR)</option>
                            <option value="PQC">Phú Quốc (PQC)</option>
                            <option value="HPH">Hải Phòng (HPH)</option>
                            <option value="VCA">Cần Thơ (VCA)</option>
                            <option value="DLI">Đà Lạt (DLI)</option>
                        </optgroup>
                        <optgroup label="ĐÔNG NAM Á">
                            <option value="BKK">Bangkok (BKK)</option>
                            <option value="KUL">Kuala Lumpur (KUL)</option>
                            <option value="SIN">Singapore (SIN)</option>
                            <option value="MNL">Manila (MNL)</option>
                            <option value="CGK">Jakarta (CGK)</option>
                            <option value="PNH">Phnom Penh (PNH)</option>
                            <option value="RGN">Yangon (RGN)</option>
                            <option value="VTE">Vientiane (VTE)</option>
                        </optgroup>
                        <optgroup label="ĐÔNG Á">
                            <option value="NRT">Tokyo Narita (NRT)</option>
                            <option value="ICN">Seoul Incheon (ICN)</option>
                            <option value="HKG">Hong Kong (HKG)</option>
                            <option value="PEK">Bắc Kinh (PEK)</option>
                        </optgroup>
                        <optgroup label="BẮC MỸ">
                            <option value="JFK">New York JFK (JFK)</option>
                            <option value="LAX">Los Angeles (LAX)</option>
                            <option value="YYZ">Toronto Pearson (YYZ)</option>
                        </optgroup>
                        <optgroup label="CHÂU ÂU">
                            <option value="LHR">London Heathrow (LHR)</option>
                            <option value="CDG">Paris Charles de Gaulle (CDG)</option>
                            <option value="FRA">Frankfurt (FRA)</option>
                            <option value="AMS">Amsterdam (AMS)</option>
                            <option value="MAD">Madrid (MAD)</option>
                            <option value="FCO">Rome Fiumicino (FCO)</option>
                            <option value="BER">Berlin Brandenburg (BER)</option>
                            <option value="VIE">Vienna (VIE)</option>
                            <option value="ZRH">Zurich (ZRH)</option>
                        </optgroup>
                    </select>
                </div>

                <!-- Ngày đi - Ngày về -->
                <div class="form-group">
                    <label>Ngày đi:</label>
                    <input type="date" name="departure_date" required>
                    <label id="return_label" style="display:none;">Ngày về:</label>
                    <input type="date" name="return_date" id="return_date" style="display:none;">
                </div>

                <!-- Số lượng hành khách -->
                <div class="form-group passengers">
                    <div>
                        <label>Người lớn:</label>
                        <input type="number" name="adults" value="1" min="1">
                    </div>
                    <div>
                        <label>Trẻ em:</label>
                        <input type="number" name="children" value="0" min="0">
                    </div>
                    <div>
                        <label>Em bé:</label>
                        <input type="number" name="infants" value="0" min="0">
                    </div>
                </div>

                <!-- Nút tìm chuyến bay -->
                <button type="submit" class="btn-submit">Tìm chuyến bay</button>
                <a href="user_page.php" class="btn-back"><i class="fas fa-arrow-left"></i> Quay lại</a>
            </form>
        </div>
    </div>

    <script>
    function toggleReturnDate(show) {
        document.getElementById('return_label').style.display = show ? 'inline-block' : 'none';
        document.getElementById('return_date').style.display = show ? 'inline-block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        var ticketType = document.querySelector('input[name="ticket_type"]:checked').value;
        if (ticketType === 'round_trip') {
            toggleReturnDate(true);
        } else {
            toggleReturnDate(false);
        }
    });
    </script>
</body>
</html>