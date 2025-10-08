<?php
require_once 'app/config/database.php';
session_start();

// Kiểm tra nhân viên đã đăng nhập chưa
if (!isset($_SESSION['staff_id'])) {
    echo "<script>alert('❌ Bạn chưa đăng nhập!'); window.location.href='login_register.php';</script>";
    exit;
}

$staff_id = $_SESSION['staff_id']; 
$accountInfo = [];
$updateSuccess = false;
$passwordUpdateSuccess = false;
$users = [];
$flights = [];

// Truy xuất danh sách vé từ bảng hoadon
try {
    $stmt = $pdo->prepare("SELECT * FROM hoadon ORDER BY created_at DESC");
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
try {
    $stmt = $pdo->prepare("SELECT * FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM airlines");
    $stmt->execute();
    $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE id = ?");
    $stmt->execute([$staff_id]);
    $accountInfo = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}

// Xử lý tạo vé mới
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_ticket'])) {
    $ma_hoadon = $_POST['ma_hoadon'];
    $name = $_POST['name'];
    $ticket_type = $_POST['ticket_type'];
    $departure = $_POST['departure'];
    $destination = $_POST['destination'];
    $departure_date = $_POST['departure_date'];
    $return_date = $_POST['return_date'] ?: null;
    $adults = $_POST['adults'];
    $children = $_POST['children'];
    $infants = $_POST['infants'];
    $airline_name = $_POST['airline_name'];
    $airline_code = $_POST['airline_code'];
    $country = $_POST['country'];
    $flight_start_time = $_POST['flight_start_time'];
    $flight_end_time = $_POST['flight_end_time'];
    $ticket_price = $_POST['ticket_price'];

    try {
        $stmt = $pdo->prepare("INSERT INTO hoadon (ma_hoadon, name, ticket_type, departure, destination, departure_date, return_date, adults, children, infants, airline_name, airline_code, country, flight_start_time, flight_end_time, ticket_price, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$ma_hoadon, $name, $ticket_type, $departure, $destination, $departure_date, $return_date, $adults, $children, $infants, $airline_name, $airline_code, $country, $flight_start_time, $flight_end_time, $ticket_price]);

        // Trả về dữ liệu vé mới để hiển thị
        $new_ticket = [
            'ma_hoadon' => $ma_hoadon,
            'name' => $name,
            'ticket_type' => $ticket_type,
            'departure' => $departure,
            'destination' => $destination,
            'departure_date' => $departure_date,
            'return_date' => $return_date,
            'adults' => $adults,
            'children' => $children,
            'infants' => $infants,
            'airline_name' => $airline_name,
            'airline_code' => $airline_code,
            'flight_start_time' => $flight_start_time,
            'flight_end_time' => $flight_end_time,
            'ticket_price' => $ticket_price,
            'created_at' => date('Y-m-d H:i:s') // Thời gian hiện tại
        ];

        echo json_encode(['status' => 'success', 'message' => 'Tạo vé thành công!', 'ticket' => $new_ticket]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()]);
        exit;
    }
}

// Cập nhật thông tin tài khoản
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_account'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    try {
        $stmt = $pdo->prepare("UPDATE staff SET full_name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$full_name, $email, $phone, $staff_id]);
        $_SESSION['account_updated'] = true;
        $updateSuccess = true;

        $stmt = $pdo->prepare("SELECT * FROM staff WHERE id = ?");
        $stmt->execute([$staff_id]);
        $accountInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Lỗi cập nhật: " . $e->getMessage();
    }
}

// Đổi mật khẩu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!isset($_SESSION['staff_id'])) {
        echo "<script>alert('❌ Bạn chưa đăng nhập!'); window.location.href='login_register.php';</script>";
        exit;
    }

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT password FROM staff WHERE id = ?");
        $stmt->execute([$staff_id]);
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($staff && password_verify($current_password, $staff['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE staff SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $staff_id]);
                $_SESSION['password_updated'] = true;
                echo "<script>alert('✔ Đổi mật khẩu thành công! Vui lòng đăng nhập lại bằng mật khẩu mới.'); window.location.href='login_register.php';</script>";
                session_destroy();
                exit;
            } else {
                echo "<script>alert('❌ Mật khẩu mới không trùng khớp!');</script>";
            }
        } else {
            echo "<script>alert('❌ Mật khẩu hiện tại không đúng!');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('❌ Lỗi đổi mật khẩu: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Khách hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin_style.css">
    <link rel="stylesheet" href="assets/css/staff_style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
   css

Thu gọn

Bọc lại

Sao chép
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Arial', sans-serif;
}

/* Sidebar */
.sidebar {
    width: 260px;
    height: 100vh;
    background: linear-gradient(180deg, #2d3748, #4a5568); /* Tông xám xanh dịu */
    position: fixed;
    top: 0;
    left: 0;
    padding: 20px;
    color:rgb(226, 229, 233);
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
    transition: width 0.3s ease;
}

.sidebar h2 {
    font-size: 22px;
    text-align: center;
    margin-bottom: 30px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color:rgb(232, 235, 240);
}

.sidebar ul {
    list-style: none;
}

.sidebar ul li {
    margin: 15px 0;
}

.sidebar ul li a {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color:rgb(239, 242, 246);
    text-decoration: none;
    font-size: 16px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.sidebar ul li a i {
    margin-right: 10px;
    font-size: 18px;
    width: 20px;
    text-align: center;
}

.sidebar ul li a:hover {
    background-color: #718096; /* Xám xanh nhạt khi hover */
    transform: translateX(5px);
}

.sidebar ul li a.active {
    background-color: #4a5568;
    font-weight: bold;
}

/* Main Container */
.container.container__manager {
    margin-left: 260px;
    padding: 30px;
    background:rgb(234, 240, 243); /* Nền trắng xám nhạt dịu */
    min-height: 100vh;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    width: calc(100% - 260px);
    overflow-x: auto;
    transition: opacity 0.3s ease;
}

.container h2 {
    font-size: 26px;
    color: #2d3748; /* Xám đậm dịu */
    margin-bottom: 20px;
    text-align: center;
}

/* Search Container */
.search-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-bottom: 25px;
}

#search-box, #flight-search-box, #ticket-search-box {
    padding: 10px;
    width: 300px;
    border: 1px solidrgb(201, 216, 236); /* Viền xám nhạt */
    border-radius: 6px;
    font-size: 14px;
    background: #fff;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

#search-box:focus, #flight-search-box:focus, #ticket-search-box:focus {
    border-color: #4a5568; /* Xám trung tính khi focus */
    box-shadow: 0 0 5px rgba(74, 85, 104, 0.2);
    outline: none;
}

#search-btn, #flight-search-btn, #ticket-search-btn, #create-ticket-btn {
    padding: 10px 20px;
    background-color: #4a5568; /* Xám đậm dịu */
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

#search-btn:hover, #flight-search-btn:hover, #ticket-search-btn:hover, #create-ticket-btn:hover {
    background-color: #2d3748; /* Xám đậm hơn khi hover */
    transform: scale(1.05);
}

/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

th, td {
    padding: 12px 15px;
    text-align: left;
    font-size: 14px;
}

th {
    background-color: #4a5568; /* Xám đậm dịu cho tiêu đề bảng */
    color:rgb(183, 199, 218);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

td {
    border-bottom: 1px solidrgb(170, 193, 216); /* Viền xám nhạt */
    color: #2d3748; /* Chữ xám đậm dịu */
}

tr:nth-child(even) {
    background-color:rgb(189, 212, 227); /* Nền xám trắng nhạt */
}

tr:hover {
    background-color:rgb(178, 198, 219); /* Hover xám nhạt */
    transition: background-color 0.2s ease;
}

/* Ticket Form Container */
#ticket-form-container {
    display: none;
    margin-bottom: 30px;
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    animation: fadeIn 0.5s ease;
}

#ticket-form-container h3 {
    font-size: 20px;
    color: #2d3748;
    margin-bottom: 20px;
    text-align: center;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 14px;
    color: #4a5568;
    margin-bottom: 5px;
}

.form-group input, .form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solidrgb(197, 212, 230);
    border-radius: 6px;
    font-size: 14px;
    background: #fff;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-group input:focus, .form-group select:focus {
    border-color: #4a5568;
    box-shadow: 0 0 5px rgba(74, 85, 104, 0.2);
    outline: none;
}

.form-actions {
    text-align: center;
    margin-top: 20px;
}

.form-actions button {
    padding: 10px 25px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.btn-submit {
    background-color: #38b2ac; /* Xanh ngọc dịu */
    color: #fff;
}

.btn-submit:hover {
    background-color: #319795; /* Xanh ngọc đậm hơn */
    transform: scale(1.05);
}

.btn-cancel {
    background-color: #f687b3; /* Hồng nhạt dịu */
    color: #fff;
}

.btn-cancel:hover {
    background-color: #ed64a6; /* Hồng đậm hơn */
    transform: scale(1.05);
}

/* Account and Password Form */
.account-form, .password-form {
    max-width: 500px;
    margin: 0 auto;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    animation: fadeIn 0.5s ease;
}

.account-form h2, .password-form h2 {
    font-size: 24px;
    color: #2d3748;
    margin-bottom: 20px;
    text-align: center;
}

.btn-update {
    display: block;
    width: 100%;
    padding: 12px;
    background-color: #4a5568;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.btn-update:hover {
    background-color: #2d3748;
    transform: scale(1.05);
}

.success-message {
    color: #38b2ac;
    text-align: center;
    margin-top: 10px;
    font-size: 14px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        width: 70px;
        padding: 10px;
    }

    .sidebar h2 {
        font-size: 18px;
        margin-bottom: 20px;
    }

    .sidebar ul li a {
        padding: 10px;
        justify-content: center;
    }

    .sidebar ul li a span {
        display: none;
    }

    .sidebar ul li a i {
        margin-right: 0;
    }

    .container.container__manager {
        margin-left: 70px;
        width: calc(100% - 70px);
    }

    .search-container {
        flex-direction: column;
    }

    #search-box, #flight-search-box, #ticket-search-box {
        width: 100%;
    }

    table {
        font-size: 12px;
    }
}
    </style>
</head>
<body>
<div class="sidebar"> 
    <h2>SEVEN AIRLINE MANAGER TICKET</h2>
    <ul>
        <li><a href="#" id="manage-customers-btn"><i class="fas fa-users"></i> Quản lý khách hàng</a></li>
        <li><a href="#" id="manage-flights-btn"><i class="fas fa-plane"></i> Tra cứu chuyến bay</a></li>
        <li><a href="#" id="manage-tickets-btn"><i class="fas fa-ticket-alt"></i> Quản lý vé</a></li>
        <li><a href="#" id="account-info-btn"><i class="fas fa-user"></i> Thông tin tài khoản</a></li>
        <li><a href="#" id="password-change-btn"><i class="fas fa-lock"></i> Đổi mật khẩu</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
    </ul>
</div>

<main id="customer-list" class="container container__manager" style="display: none;">
    <h2 style="text-align: center;">Danh sách Khách hàng</h2>
    <div class="search-container">
        <input type="text" id="search-box" placeholder="Nhập số điện thoại cần tìm...">
        <button id="search-btn">Tìm</button>
    </div>
    <table id="customer-table" border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên đăng nhập</th>
                <th>Họ và tên</th>
                <th>Email</th>
                <th>Số điện thoại</th>
                <th>Ngày đăng ký</th>
                <th>Ngân hàng</th>
                <th>Số tài khoản/số thẻ</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                <td><?php echo htmlspecialchars($user['bank_name']); ?></td>
                <td><?php echo htmlspecialchars($user['bank_account_number']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>

<main id="flight-list" class="container container__manager" style="display: none;">
    <h2 style="text-align: center;">Danh sách Chuyến bay</h2>
    <div class="search-container">
        <input type="text" id="flight-search-box" placeholder="Nhập mã chuyến bay cần tìm...">
        <button id="flight-search-btn">Tìm</button>
    </div>
    <table id="flight-table" border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên hãng bay</th>
                <th>Mã chuyến bay</th>
                <th>Quốc gia</th>
                <th>Giờ khởi hành</th>
                <th>Giờ kết thúc</th>
                <th>Giá vé</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($flights as $flight): ?>
            <tr>
                <td><?php echo htmlspecialchars($flight['airline_id']); ?></td>
                <td><?php echo htmlspecialchars($flight['airline_name']); ?></td>
                <td><?php echo htmlspecialchars($flight['airline_code']); ?></td>
                <td><?php echo htmlspecialchars($flight['country']); ?></td>
                <td><?php echo htmlspecialchars($flight['flight_start_time']); ?></td>
                <td><?php echo htmlspecialchars($flight['flight_end_time']); ?></td>
                <td><?php echo number_format($flight['ticket_price'], 2); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>

<main id="account-info" class="container" style="display: none;">
    <form method="post" class="account-form">
        <h2>Thông Tin Tài Khoản</h2>
        <div class="form-group">
            <label for="full_name"><i class="fas fa-user"></i> Họ và Tên:</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($accountInfo['full_name'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="email"><i class="fas fa-envelope"></i> Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($accountInfo['email'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="phone"><i class="fas fa-phone"></i> Số Điện Thoại:</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($accountInfo['phone'] ?? ''); ?>" required>
        </div>
        <button type="submit" class="btn-update" name="update_account"><i class="fas fa-save"></i> Cập Nhật</button>
        <?php if ($updateSuccess): ?>
            <p id="success-message" class="success-message">✔ Cập nhật thành công!</p>
        <?php endif; ?>
    </form>
</main>

<main id="change-password" class="container" style="display: none;">
    <form method="post" class="password-form">
        <h2>Đổi Mật Khẩu</h2>
        <div class="form-group">
            <label for="current_password"><i class="fas fa-lock"></i> Mật khẩu hiện tại:</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>
        <div class="form-group">
            <label for="new_password"><i class="fas fa-key"></i> Mật khẩu mới:</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password"><i class="fas fa-key"></i> Xác nhận mật khẩu:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn-update" name="change_password"><i class="fas fa-check"></i> Cập Nhật Mật Khẩu</button>
        <?php if ($passwordUpdateSuccess): ?>
            <p class="success-message" id="password-success-message">✔ Đổi mật khẩu thành công!</p>
        <?php endif; ?>
    </form>
</main>

<main id="ticket-management" class="container container__manager" style="display: none;">
    <h2 style="text-align: center;">Danh Sách Vé Đã Đặt</h2>
    <div class="search-container">
        <input type="text" id="ticket-search-box" placeholder="Nhập mã hóa đơn hoặc tên khách hàng...">
        <button id="ticket-search-btn">Tìm</button>
        <button id="create-ticket-btn">Tạo vé mới</button>
    </div>
    <div id="ticket-form-container">
        <h3>Tạo vé mới</h3>
        <form id="ticket-form">
            <div class="form-group">
                <label for="ma_hoadon">Mã hóa đơn:</label>
                <input type="text" id="ma_hoadon" name="ma_hoadon" required>
            </div>
            <div class="form-group">
                <label for="name">Tên khách hàng:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="ticket_type">Loại vé:</label>
                <select id="ticket_type" name="ticket_type" required>
                    <option value="one_way">Một chiều</option>
                    <option value="round_trip">Khứ hồi</option>
                </select>
            </div>
            <div class="form-group">
                <label for="departure">Điểm đi:</label>
                <input type="text" id="departure" name="departure" required>
            </div>
            <div class="form-group">
                <label for="destination">Điểm đến:</label>
                <input type="text" id="destination" name="destination" required>
            </div>
            <div class="form-group">
                <label for="departure_date">Ngày đi:</label>
                <input type="date" id="departure_date" name="departure_date" required>
            </div>
            <div class="form-group">
                <label for="return_date">Ngày về (nếu có):</label>
                <input type="date" id="return_date" name="return_date">
            </div>
            <div class="form-group">
                <label for="adults">Số người lớn:</label>
                <input type="number" id="adults" name="adults" min="0" value="0">
            </div>
            <div class="form-group">
                <label for="children">Số trẻ em:</label>
                <input type="number" id="children" name="children" min="0" value="0">
            </div>
            <div class="form-group">
                <label for="infants">Số em bé:</label>
                <input type="number" id="infants" name="infants" min="0" value="0">
            </div>
            <div class="form-group">
                <label for="airline_name">Tên hãng bay:</label>
                <input type="text" id="airline_name" name="airline_name" required>
            </div>
            <div class="form-group">
                <label for="airline_code">Mã hãng bay:</label>
                <input type="text" id="airline_code" name="airline_code" required>
            </div>
            <div class="form-group">
                <label for="country">Quốc gia:</label>
                <input type="text" id="country" name="country" required>
            </div>
            <div class="form-group">
                <label for="flight_start_time">Giờ khởi hành:</label>
                <input type="time" id="flight_start_time" name="flight_start_time" required>
            </div>
            <div class="form-group">
                <label for="flight_end_time">Giờ đến:</label>
                <input type="time" id="flight_end_time" name="flight_end_time" required>
            </div>
            <div class="form-group">
                <label for="ticket_price">Tổng tiền (VND):</label>
                <input type="number" id="ticket_price" name="ticket_price" min="0" step="0.01" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-submit">Lưu</button>
                <button type="button" class="btn-cancel" id="cancel-ticket-btn">Hủy</button>
            </div>
        </form>
    </div>
    <table id="ticket-table" border="1">
        <thead>
            <tr>
                <th>Mã Hóa Đơn</th>
                <th>Khách Hàng</th>
                <th>Điểm Đi</th>
                <th>Điểm Đến</th>
                <th>Loại Vé</th>
                <th>Ngày Đi</th>
                <th>Ngày Về</th>
                <th>Giờ Khởi Hành</th>
                <th>Giờ Đến</th>
                <th>Hãng Bay</th>
                <th>Ngày Đặt</th>
                <th>Người Lớn</th>
                <th>Trẻ Em</th>
                <th>Em Bé</th>
                <th>Tổng Tiền</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($tickets as $ticket): ?>
            <tr>
                <td><?php echo htmlspecialchars($ticket['ma_hoadon']); ?></td>
                <td><?php echo htmlspecialchars($ticket['name']); ?></td>
                <td><?php echo htmlspecialchars($ticket['departure']); ?></td>
                <td><?php echo htmlspecialchars($ticket['destination']); ?></td>
                <td><?php echo $ticket['ticket_type'] === 'one_way' ? 'Một chiều' : 'Khứ hồi'; ?></td>
                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($ticket['departure_date']))); ?></td>
                <td><?php echo $ticket['return_date'] ? htmlspecialchars(date('d/m/Y', strtotime($ticket['return_date']))) : 'N/A'; ?></td>
                <td><?php echo htmlspecialchars($ticket['flight_start_time']); ?></td>
                <td><?php echo htmlspecialchars($ticket['flight_end_time']); ?></td>
                <td><?php echo htmlspecialchars($ticket['airline_name'] . ' (' . $ticket['airline_code'] . ')'); ?></td>
                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($ticket['created_at']))); ?></td>
                <td><?php echo htmlspecialchars($ticket['adults'] ?? '0'); ?></td>
                <td><?php echo htmlspecialchars($ticket['children'] ?? '0'); ?></td>
                <td><?php echo htmlspecialchars($ticket['infants'] ?? '0'); ?></td>
                <td><?php echo number_format($ticket['ticket_price'], 0, ',', '.') . ' đ'; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Hàm chuyển đổi section
    function showSection(sectionId) {
        $(".container").hide();
        $(sectionId).show();
    }

    // Ẩn tất cả sections khi load trang
    $(".container").hide();

    // Customer search
    $('#search-btn').on('click', function() {
        showSection('#customer-list');
        let input = $('#search-box').val().trim().toLowerCase();
        $('#customer-table tbody tr').each(function() {
            let rowText = $(this).text().trim().toLowerCase();
            $(this).toggle(rowText.includes(input));
        });
    });

    // Flight search
    $('#flight-search-btn').on('click', function() {
        showSection('#flight-list');
        let input = $('#flight-search-box').val().trim().toLowerCase();
        $('#flight-table tbody tr').each(function() {
            let rowText = $(this).text().trim().toLowerCase();
            $(this).toggle(rowText.includes(input));
        });
    });

    // Ticket search
    $('#ticket-search-btn').on('click', function() {
        showSection('#ticket-management');
        let input = $('#ticket-search-box').val().trim().toLowerCase();
        $('#ticket-table tbody tr').each(function() {
            let rowText = $(this).text().trim().toLowerCase();
            $(this).toggle(rowText.includes(input));
        });
    });

    // Sidebar menu handlers
    $("#manage-customers-btn").click(function(e) {
        e.preventDefault();
        showSection("#customer-list");
    });

    $("#manage-flights-btn").click(function(e) {
        e.preventDefault();
        showSection("#flight-list");
    });

    $("#manage-tickets-btn").click(function(e) {
        e.preventDefault();
        showSection("#ticket-management");
    });

    $("#account-info-btn").click(function(e) {
        e.preventDefault();
        showSection("#account-info");
    });

    $("#password-change-btn").click(function(e) {
        e.preventDefault();
        showSection("#change-password");
    });

    // Hiển thị/ẩn form tạo vé mới
    $("#create-ticket-btn").click(function() {
        $("#ticket-form-container").slideToggle();
    });

    // Hủy form tạo vé
    $("#cancel-ticket-btn").click(function() {
        $("#ticket-form-container").slideUp();
        $("#ticket-form")[0].reset();
    });

    // Xử lý submit form tạo vé mới
    $("#ticket-form").submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: window.location.href,
            type: "POST",
            data: $(this).serialize() + "&create_ticket=1",
            dataType: "json",
            success: function(response) {
                if (response.status === "success") {
                    alert(response.message);
                    $("#ticket-form-container").slideUp();
                    $("#ticket-form")[0].reset();

                    // Thêm dòng mới vào bảng từ dữ liệu server
                    let ticket = response.ticket;
                    let newRow = `
                        <tr>
                            <td>${ticket.ma_hoadon}</td>
                            <td>${ticket.name}</td>
                            <td>${ticket.departure}</td>
                            <td>${ticket.destination}</td>
                            <td>${ticket.ticket_type === "one_way" ? "Một chiều" : "Khứ hồi"}</td>
                            <td>${new Date(ticket.departure_date).toLocaleDateString("vi-VN")}</td>
                            <td>${ticket.return_date ? new Date(ticket.return_date).toLocaleDateString("vi-VN") : "N/A"}</td>
                            <td>${ticket.flight_start_time}</td>
                            <td>${ticket.flight_end_time}</td>
                            <td>${ticket.airline_name} (${ticket.airline_code})</td>
                            <td>${new Date(ticket.created_at).toLocaleString("vi-VN")}</td>
                            <td>${ticket.adults || 0}</td>
                            <td>${ticket.children || 0}</td>
                            <td>${ticket.infants || 0}</td>
                            <td>${parseFloat(ticket.ticket_price).toLocaleString("vi-VN")} đ</td>
                        </tr>
                    `;
                    $("#ticket-table tbody").prepend(newRow);
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                alert("Lỗi kết nối đến server: " + error);
            }
        });
    });

    // Hiển thị thông báo thành công nếu có
    if ($("#success-message").length) {
        $("#success-message").fadeIn(500).delay(2000).fadeOut(500);
    }
    if ($("#password-success-message").length) {
        $("#password-success-message").fadeIn(500).delay(2000).fadeOut(500);
    }
});
</script>
</body>
</html>