<?php
include('app/config/database.php');

// Truy vấn dữ liệu chuyến bay
$sql = "SELECT airline_id, airline_name, airline_code, country, flight_start_time, flight_end_time, ticket_price FROM airlines";
$stmt = $pdo->prepare($sql);
$stmt->execute();

$data = $stmt->fetchAll();

if (count($data) > 0) {
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không có dữ liệu']);
}
?>
