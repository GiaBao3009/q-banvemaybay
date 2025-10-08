<?php
include('app/config/database.php');

if (isset($_POST['id'])) {
    $airline_id = $_POST['id'];

    // Truy vấn xóa chuyến bay theo ID
    $sql = "DELETE FROM airlines WHERE airline_id = :airline_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':airline_id', $airline_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa chuyến bay']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
}
?>
