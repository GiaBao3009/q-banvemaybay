<?php
session_start();
require 'app/config/database.php'; // Kết nối database

if (!isset($_SESSION['customer_username'])) {
    header('Location: customer_auth.php');
    exit;
}

$username = $_SESSION['customer_username'];

// Lấy dữ liệu từ form
$ticket_type = $_POST['ticket_type'];
$departure = $_POST['departure'];
$destination = $_POST['destination'];
$departure_date = $_POST['departure_date'];
$return_date = NULL;  // Mặc định không có ngày về

// Chỉ lấy giá trị return_date nếu là vé khứ hồi
if ($ticket_type == 'round_trip') {
    $return_date = isset($_POST['return_date']) ? $_POST['return_date'] : NULL;
}

$adults = $_POST['adults'];
$children = $_POST['children'];
$infants = $_POST['infants'];

// Chuẩn bị câu lệnh SQL để lưu vào table bookings
$query = "INSERT INTO bookings (name, ticket_type, departure, destination, departure_date, return_date, adults, children, infants)
          VALUES (:name, :ticket_type, :departure, :destination, :departure_date, :return_date, :adults, :children, :infants)";

// Chuẩn bị statement
$stmt = $pdo->prepare($query);

// Liên kết giá trị vào statement
$stmt->bindParam(':name', $username);
$stmt->bindParam(':ticket_type', $ticket_type);
$stmt->bindParam(':departure', $departure);
$stmt->bindParam(':destination', $destination);
$stmt->bindParam(':departure_date', $departure_date);
$stmt->bindParam(':return_date', $return_date);
$stmt->bindParam(':adults', $adults);
$stmt->bindParam(':children', $children);
$stmt->bindParam(':infants', $infants);

try {
    // Thực thi câu lệnh SQL
    $stmt->execute();
    // Hiển thị thông báo thành công
    $success_message = "Đặt vé thành công!";
    // Chuyển hướng tới trang search_flight.php sau khi thành công
    header('Location: search_flight.php?success=' . urlencode($success_message));
    exit;
} catch (PDOException $e) {
    // Hiển thị thông báo lỗi nếu có
    $error_message = "Lỗi: " . $e->getMessage();
    // Quay lại trang booking.php với thông báo lỗi
    header('Location: booking.php?error=' . urlencode($error_message));
    exit;
}
?>
