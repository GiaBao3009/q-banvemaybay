<?php
header('Content-Type: application/json');
require_once 'app/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma_hoadon = $_POST['ma_hoadon'] ?? '';
    $column_name = $_POST['column_name'] ?? '';
    $new_value = $_POST['new_value'] ?? '';

    if (empty($ma_hoadon) || empty($column_name) || $new_value === '') {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ!']);
        exit;
    }

    // List of allowed columns to prevent SQL injection
    $allowed_columns = [
        'ma_hoadon', 'name', 'ticket_type', 'departure', 'destination', 
        'departure_date', 'return_date', 'adults', 'children', 'infants', 
        'airline_name', 'flight_start_time', 'flight_end_time', 'ticket_price'
    ];

    if (!in_array($column_name, $allowed_columns)) {
        echo json_encode(['success' => false, 'message' => 'Cột không hợp lệ!']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE hoadon SET $column_name = :new_value WHERE ma_hoadon = :ma_hoadon");
        $stmt->bindParam(':new_value', $new_value);
        $stmt->bindParam(':ma_hoadon', $ma_hoadon);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ!']);
}
?>