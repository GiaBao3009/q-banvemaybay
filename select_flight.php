<?php
session_start();

if (!isset($_SESSION['customer_username'])) {
    header('Location: customer_auth.php');
    exit;
}

require_once 'app/config/database.php'; // Kết nối PDO sẵn có

// Lấy dữ liệu từ form
$customer_username = $_SESSION['customer_username'];
$full_name         = $_POST['fullname'];
$departure         = $_POST['departure'];
$arrival           = $_POST['arrival'];
$flight_date       = $_POST['flight_date'];
$return_date       = !empty($_POST['return_date']) ? $_POST['return_date'] : null;
$ticket_class      = $_POST['ticket_class'];
$adults            = (int)$_POST['adults'];
$children          = (int)$_POST['children'];
$infants           = (int)$_POST['infants'];
$baggage           = (int)$_POST['baggage'];
$seat              = $_POST['seat'];
$note              = $_POST['note'];

// Kiểm tra dữ liệu bắt buộc
if (empty($full_name) || empty($departure) || empty($arrival) || empty($flight_date) || empty($ticket_class) || empty($seat)) {
    echo "<script>alert('Vui lòng nhập đầy đủ thông tin cần thiết!'); window.history.back();</script>";
    exit;
}

try {
    // Tạo câu lệnh SQL với tham số ràng buộc
    $sql = "INSERT INTO Datve (
                customer_username, 
                full_name, 
                departure, 
                arrival, 
                flight_date, 
                return_date, 
                ticket_class, 
                adults, 
                children, 
                infants, 
                baggage, 
                seat, 
                note
            ) VALUES (
                :customer_username, 
                :full_name, 
                :departure, 
                :arrival, 
                :flight_date, 
                :return_date, 
                :ticket_class, 
                :adults, 
                :children, 
                :infants, 
                :baggage, 
                :seat, 
                :note
            )";

    // Chuẩn bị statement
    $stmt = $pdo->prepare($sql);

    // Bind tham số
    $stmt->bindParam(':customer_username', $customer_username);
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':departure', $departure);
    $stmt->bindParam(':arrival', $arrival);
    $stmt->bindParam(':flight_date', $flight_date);
    $stmt->bindParam(':return_date', $return_date);
    $stmt->bindParam(':ticket_class', $ticket_class);
    $stmt->bindParam(':adults', $adults, PDO::PARAM_INT);
    $stmt->bindParam(':children', $children, PDO::PARAM_INT);
    $stmt->bindParam(':infants', $infants, PDO::PARAM_INT);
    $stmt->bindParam(':baggage', $baggage, PDO::PARAM_INT);
    $stmt->bindParam(':seat', $seat);
    $stmt->bindParam(':note', $note);

    // Thực thi câu lệnh
    if ($stmt->execute()) {
        echo "<script>alert('✅ Đặt vé thành công!'); window.location.href='user_page.php';</script>";
    } else {
        echo "<script>alert('❌ Đặt vé thất bại, vui lòng thử lại!'); window.history.back();</script>";
    }

} catch (PDOException $e) {
    echo "❌ Lỗi truy vấn: " . $e->getMessage();
    exit;
}
?>
