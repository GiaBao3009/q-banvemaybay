<?php
header('Content-Type: application/json');
require_once 'app/config/database.php';

try {
    // Lọc dữ liệu đầu vào để tránh SQL Injection
    $year = isset($_GET['year']) ? filter_var($_GET['year'], FILTER_SANITIZE_NUMBER_INT) : '';
    $month = isset($_GET['month']) ? filter_var($_GET['month'], FILTER_SANITIZE_NUMBER_INT) : '';

    // Xây dựng câu truy vấn
    $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, SUM(ticket_price) AS total_revenue FROM hoadon";
    $conditions = [];
    $params = [];

    if ($year) {
        $conditions[] = "YEAR(created_at) = :year";
        $params['year'] = $year;
    }
    if ($month) {
        $conditions[] = "MONTH(created_at) = :month";
        $params['month'] = $month;
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    $sql .= " GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month ASC";

    // Chuẩn bị và thực thi truy vấn
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => &$val) {
        $stmt->bindParam(":$key", $val, PDO::PARAM_INT);
    }
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Xử lý dữ liệu để tính sự thay đổi theo tháng
    $data = [];
    $prev = null;
    foreach ($results as $row) {
        $curr = (float)$row['total_revenue'];
        $change = $prev !== null ? $curr - $prev : null;
        $data[] = [
            'month' => $row['month'],
            'total_revenue' => $curr,
            'change' => $change,
            'change_class' => $change === null ? 'change-neutral' : ($change > 0 ? 'change-up' : 'change-down')
        ];
        $prev = $curr;
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>