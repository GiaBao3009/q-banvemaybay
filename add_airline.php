<?php
include('app/config/database.php');

// Kiểm tra nếu có dữ liệu gửi lên từ form
if (isset($_POST['airline_name']) && isset($_POST['airline_code']) && isset($_POST['country']) && isset($_POST['flight_start_time']) && isset($_POST['flight_end_time']) && isset($_POST['ticket_price'])) {
    // Lấy dữ liệu từ POST
    $airlineName = $_POST['airline_name'];
    $airlineCode = $_POST['airline_code'];
    $country = $_POST['country'];
    $flightStartTime = $_POST['flight_start_time'];
    $flightEndTime = $_POST['flight_end_time'];
    $ticketPrice = $_POST['ticket_price'];

    // Truy vấn thêm chuyến bay vào cơ sở dữ liệu
    $sql = "INSERT INTO airlines (airline_name, airline_code, country, flight_start_time, flight_end_time, ticket_price) 
            VALUES (:airline_name, :airline_code, :country, :flight_start_time, :flight_end_time, :ticket_price)";
    
    $stmt = $pdo->prepare($sql);
    
    // Bind các tham số
    $stmt->bindParam(':airline_name', $airlineName);
    $stmt->bindParam(':airline_code', $airlineCode);
    $stmt->bindParam(':country', $country);
    $stmt->bindParam(':flight_start_time', $flightStartTime);
    $stmt->bindParam(':flight_end_time', $flightEndTime);
    $stmt->bindParam(':ticket_price', $ticketPrice);
    
    if ($stmt->execute()) {
        // Nếu thêm thành công
        echo json_encode(['success' => true]);
    } else {
        // Nếu xảy ra lỗi khi thêm
        echo json_encode(['success' => false, 'message' => 'Không thể thêm chuyến bay']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
}
?>
