<?php
session_start();
require 'app/config/database.php'; // Kết nối PDO trong file này, kiểm tra $pdo có hoạt động không.

// Mặc định hiển thị form đăng nhập
$formState = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($action === 'register') {
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $bank_name = trim($_POST['bank_name'] ?? '');
        $bank_account_number = trim($_POST['bank_account_number'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');

        // Kiểm tra tài khoản hoặc email đã tồn tại chưa
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $_SESSION['error_message'] = 'Tài khoản hoặc email đã tồn tại!';
            $formState = 'register';
        } else {
            // Hash mật khẩu và lưu dữ liệu
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare('INSERT INTO users 
                (username, password, email, phone, full_name, bank_name, bank_account_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?)');

            $isInserted = $stmt->execute([
                $username, $hashedPassword, $email, $phone, $full_name, $bank_name, $bank_account_number
            ]);

            if ($isInserted) {
                $_SESSION['success_message'] = 'Đăng ký thành công! Vui lòng đăng nhập.';
                header('Location: customer_auth.php');
                exit;
            } else {
                $_SESSION['error_message'] = 'Đăng ký thất bại! Vui lòng thử lại.';
                $formState = 'register';
            }
        }
    }

    if ($action === 'login') {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['customer_username'] = $username;
            header('Location: user_page.php');
            exit;
        } else {
            $_SESSION['error_message'] = 'Sai tên đăng nhập hoặc mật khẩu!';
            $formState = 'login';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khách hàng - Đăng nhập/Đăng ký</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
       body {
    background: url('assets/images/hihi.jpg') no-repeat center center fixed;
    background-size: cover;
    font-family: 'Segoe UI', Arial, sans-serif;
    margin: 0;
    padding: 0;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: #2d3748;
}

.header-container {
    background: linear-gradient(90deg, #2b6cb0, #4c9eea); /* Xanh dương dịu */
    color: #fff;
    text-align: center;
    padding: 20px 0;
    font-size: 24px;
    font-weight: bold;
    width: 100%;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

main {
    margin-top: 80px;
    width: 100%;
    display: flex;
    justify-content: center;
}

.auth-section {
    max-width: 420px;
    padding: 30px;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    text-align: center;
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.auth-section h2 {
    margin-bottom: 20px;
    color: #2b6cb0;
    font-size: 28px;
    font-weight: 600;
}

label {
    display: block;
    text-align: left;
    font-weight: 500;
    margin: 15px 0 8px;
    color: #4a5568;
    font-size: 15px;
}

input {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 15px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-sizing: border-box;
    font-size: 15px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

input:focus {
    border-color: #2b6cb0;
    outline: none;
    box-shadow: 0 0 6px rgba(43, 108, 176, 0.3);
}

button {
    background: linear-gradient(90deg, #38b2ac, #4fd1c5); /* Xanh ngọc dịu */
    color: #fff;
    border: none;
    padding: 12px;
    border-radius: 8px;
    cursor: pointer;
    width: 100%;
    font-size: 16px;
    font-weight: 500;
    transition: background 0.3s ease, transform 0.2s ease;
}

button:hover {
    background: linear-gradient(90deg, #319795, #38b2ac);
    transform: scale(1.02);
}

#forgotPasswordSection button {
    background: linear-gradient(90deg, #2b6cb0, #4c9eea);
}

#forgotPasswordSection button:hover {
    background: linear-gradient(90deg, #2c5282, #2b6cb0);
    transform: scale(1.02);
}

a {
    color: #2b6cb0;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s ease;
}

a:hover {
    color: #2c5282;
    text-decoration: underline;
}

.hidden {
    display: none;
}
    </style>
</head>
<body>
<header>
    <div class="header-container">
        <h1>Hệ Thống Bán Vé Máy Bay SEVEN AIRLINE</h1>
    </div>
</header>

<main style="background-color:unset; box-shadow:unset">
    <section class="auth-section">
        <div id="login-register-form" class="<?= ($formState === 'forgot') ? 'hidden' : '' ?>">
            <h2 id="form-title"><?= ($formState === 'login') ? 'Đăng Nhập' : 'Đăng Ký' ?></h2>

            <?php if (isset($_SESSION['error_message'])): ?>
                <p style="color: red; text-align: center;">
                    <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </p>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <p style="color: green; text-align: center;">
                    <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </p>
            <?php endif; ?>

            <form id="auth-form" action="customer_auth.php" method="POST">
                <input type="hidden" id="auth-action" name="action" value="<?= $formState ?>">

                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="username" required value="<?= htmlspecialchars($username ?? '') ?>">

                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" required>

                <div id="extra-fields" class="<?= ($formState === 'register') ? '' : 'hidden' ?>">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>">

                    <label for="phone">Số điện thoại</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($phone ?? '') ?>">

                    <label for="bank_name">Ngân hàng</label>
                    <input type="text" id="bank_name" name="bank_name" value="<?= htmlspecialchars($bank_name ?? '') ?>">

                    <label for="bank_account_number">Số tài khoản</label>
                    <input type="text" id="bank_account_number" name="bank_account_number" value="<?= htmlspecialchars($bank_account_number ?? '') ?>">
                </div>

                <button type="submit" id="submit-btn"><?= ($formState === 'login') ? 'Đăng Nhập' : 'Đăng Ký' ?></button>

                <p style="text-align: center; margin-top: 10px;">
                    <?php if ($formState === 'login'): ?>
                        Bạn chưa có tài khoản? <a href="#" onclick="toggleForm(); return false;">Đăng ký ngay</a>
                    <?php else: ?>
                        Đã có tài khoản? <a href="#" onclick="toggleForm(); return false;">Đăng nhập ngay</a>
                    <?php endif; ?>
                </p>
                <p style="text-align: center; margin-top: 10px;" id="forgot-password-link">
                    <a href="#" onclick="showForgotPassword(); return false;">Quên mật khẩu?</a>
                </p>
            </form>
        </div>

        <!-- Form quên mật khẩu -->
        <div id="forgotPasswordSection" class="hidden">
            <h2>Đặt lại mật khẩu</h2>
            <label for="forgotUsername">Tên đăng nhập</label>
            <input type="text" id="forgotUsername" name="forgotUsername" oninput="checkUsername()">
            <span id="usernameStatus" style="font-size: 14px;"></span>

            <div id="resetPasswordFields" class="hidden">
                <label for="newPassword">Mật khẩu mới</label>
                <input type="password" id="newPassword" name="newPassword">

                <label for="confirmNewPassword">Xác nhận mật khẩu mới</label>
                <input type="password" id="confirmNewPassword" name="confirmNewPassword">

                <button type="button" onclick="resetPassword()">Cập nhật mật khẩu</button>
            </div>
            <p style="text-align: center; margin-top: 10px;">
                <a href="#" onclick="backToLogin(); return false;">Quay lại đăng nhập</a>
            </p>
        </div>
    </section>
</main>

<script>
    function toggleForm() {
        const formTitle = document.getElementById('form-title');
        const actionInput = document.getElementById('auth-action');
        const submitBtn = document.getElementById('submit-btn');
        const extraFields = document.getElementById('extra-fields');
        const toggleText = document.querySelector('#login-register-form p a');

        if (actionInput.value === 'login') {
            formTitle.innerText = 'Đăng Ký';
            actionInput.value = 'register';
            submitBtn.innerText = 'Đăng Ký';
            toggleText.innerText = 'Đăng nhập ngay';
            extraFields.classList.remove('hidden');
        } else {
            formTitle.innerText = 'Đăng Nhập';
            actionInput.value = 'login';
            submitBtn.innerText = 'Đăng Nhập';
            toggleText.innerText = 'Đăng ký ngay';
            extraFields.classList.add('hidden');
        }
    }

    function showForgotPassword() {
        document.getElementById('login-register-form').classList.add('hidden');
        document.getElementById('forgotPasswordSection').classList.remove('hidden');
    }

    function backToLogin() {
        document.getElementById('forgotPasswordSection').classList.add('hidden');
        document.getElementById('login-register-form').classList.remove('hidden');
    }

    function checkUsername() {
        let username = document.getElementById("forgotUsername").value;
        let status = document.getElementById("usernameStatus");

        if (username.trim() === "") {
            status.innerHTML = "";
            document.getElementById("resetPasswordFields").classList.add("hidden");
            return;
        }

        fetch("check_username.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "username=" + encodeURIComponent(username)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "valid") {
                status.innerHTML = "<span style='color: green;'>✔️ " + data.message + "</span>";
                document.getElementById("resetPasswordFields").classList.remove("hidden");
            } else {
                status.innerHTML = "<span style='color: red;'>❌ " + data.message + "</span>";
                document.getElementById("resetPasswordFields").classList.add("hidden");
            }
        })
        .catch(error => {
            status.innerHTML = "<span style='color: red;'>Lỗi kết nối server!</span>";
        });
    }

    function resetPassword() {
        let username = document.getElementById("forgotUsername").value;
        let newPassword = document.getElementById("newPassword").value;
        let confirmNewPassword = document.getElementById("confirmNewPassword").value;

        if (newPassword.trim() === "" || confirmNewPassword.trim() === "") {
            alert("Vui lòng nhập đầy đủ mật khẩu mới!");
            return;
        }

        if (newPassword !== confirmNewPassword) {
            alert("Mật khẩu xác nhận không khớp!");
            return;
        }

        fetch("reset_password.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "username=" + encodeURIComponent(username) + "&password=" + encodeURIComponent(newPassword)
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            if (data.includes("thành công")) {
                backToLogin();
            }
        })
        .catch(error => {
            alert("Lỗi kết nối server!");
        });
    }

    window.onload = function() {
        <?php if ($formState === 'register'): ?>
            toggleForm();
        <?php endif; ?>
    };
</script>
</body>
</html>