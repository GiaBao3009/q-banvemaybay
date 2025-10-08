<?php
session_start();
require_once 'app/config/database.php'; // Kết nối database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = "❌ Vui lòng nhập đầy đủ thông tin!";
        header("Location: login_register.php");
        exit;
    }

    if ($role === 'admin') {
        // Đăng nhập Admin
        if ($username === 'admin' && $password === 'muavedi') {
            $_SESSION['username'] = 'admin';
            $_SESSION['role'] = 'admin';
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $_SESSION['error_message'] = "❌ Sai tên đăng nhập hoặc mật khẩu!";
            header("Location: login_register.php");
            exit;
        }
    } elseif ($role === 'staff') {
        // Đăng nhập Nhân viên
        $stmt = $pdo->prepare("SELECT * FROM staff WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($staff) {
            if (password_verify($password, $staff['password'])) {
                $_SESSION['username'] = $staff['username'];
                $_SESSION['role'] = 'staff';
                $_SESSION['full_name'] = $staff['full_name'];
                $_SESSION['staff_id'] = $staff['id']; // Giữ ID nhân viên
    
                // Chuyển hướng đến trang quản lý khách hàng
                header("Location: staff_manage_customers.php");
                exit;
            } else {
                $_SESSION['error_message'] = "❌ Mật khẩu không đúng!";
            }
        } else {
            $_SESSION['error_message'] = "❌ Tài khoản không tồn tại!";
        }
    
        header("Location: login_register.php");
        exit;
    } elseif ($role === 'customer') {
        // Đăng nhập Khách hàng
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($customer && password_verify($password, $customer['password'])) {
            $_SESSION['customer_username'] = $customer['username']; // Đúng tên session
            $_SESSION['role'] = 'customer';
            $_SESSION['full_name'] = $customer['full_name']; 
        
            // Chuyển hướng đến user_page.php
            header("Location: user_page.php");
            exit;        
        } else {
            $_SESSION['error_message'] = "❌ Sai tên đăng nhập hoặc mật khẩu!";
            header("Location: login_register.php");
            exit;
        }
    } else {
        $_SESSION['error_message'] = "⚠️ Vai trò không hợp lệ!";
        header("Location: login_register.php");
        exit;
    }
}
?>