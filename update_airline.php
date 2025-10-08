<?php
header('Content-Type: application/json');
require_once 'app/config/database.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $airline_id = isset($_POST['airline_id']) ? $_POST['airline_id'] : null;
    $column_name = isset($_POST['column_name']) ? $_POST['column_name'] : null;
    $new_value = isset($_POST['new_value']) ? $_POST['new_value'] : null;

    // Kiểm tra dữ liệu đầu vào
    if (!$airline_id || !$column_name || $new_value === null) {
        $response['message'] = 'Thiếu thông tin cần thiết!';
        echo json_encode($response);
        exit;
    }

    // Danh sách các cột hợp lệ để tránh SQL Injection
    $valid_columns = ['airline_name', 'airline_code', 'country', 'flight_start_time', 'flight_end_time', 'ticket_price'];
    if (!in_array($column_name, $valid_columns)) {
        $response['message'] = 'Cột không hợp lệ!';
        echo json_encode($response);
        exit;
    }

    try {
        // Chuẩn bị câu lệnh SQL
        $query = "UPDATE airlines SET $column_name = :new_value WHERE airline_id = :airline_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':new_value', $new_value);
        $stmt->bindParam(':airline_id', $airline_id, PDO::PARAM_INT);

        // Thực thi câu lệnh
        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['message'] = 'Không thể cập nhật dữ liệu!';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Phương thức không hợp lệ!';
}

echo json_encode($response);
?>