<?php
session_start();
require_once 'app/config/database.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["success" => false, "message" => "Bạn không có quyền thực hiện thao tác này!"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $staffId = intval($_POST['id']);

    try {
        $stmt = $pdo->prepare("DELETE FROM staff WHERE id = ?");
        $stmt->execute([$staffId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Không tìm thấy nhân viên để xoá!"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Yêu cầu không hợp lệ!"]);
}
?>
