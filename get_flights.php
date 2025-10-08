<?php
require 'app/config/database.php';
header('Content-Type: application/json');

if (!isset($_GET['airline_id'])) {
    echo json_encode(['error' => 'Thiếu airline_id']);
    exit;
}

$airline_id = (int) $_GET['airline_id'];

try {
    $stmt = $pdo->prepare("SELECT airline_code FROM airlines WHERE airline_id = ?");
    $stmt->execute([$airline_id]);
    $airline = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$airline) {
        echo json_encode(['error' => 'Không tìm thấy hãng hàng không với airline_id: ' . $airline_id]);
        exit;
    }

    $airline_code = $airline['airline_code'];

    $stmt = $pdo->prepare("
        SELECT airline_id, flight_start_time, flight_end_time, ticket_price 
        FROM airlines 
        WHERE airline_code = ?
        ORDER BY flight_start_time
    ");
    $stmt->execute([$airline_code]);
    $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($flights)) {
        echo json_encode(['error' => 'Không có chuyến bay nào cho hãng này']);
        exit;
    }

    echo json_encode($flights);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Lỗi truy vấn: ' . $e->getMessage()]);
    exit;
}
?>