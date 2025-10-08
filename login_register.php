<?php 
session_start();
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'muavedi');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
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
    background-color: rgba(0, 0, 0, 0.1); /* Lớp phủ mờ nhẹ để giảm chói */
}

.header-container {
    background: linear-gradient(90deg, #4a779e, #7aace3); /* Xanh dương nhạt */
    color: #f7fafc;
    text-align: center;
    padding: 15px 0;
    font-size: 22px;
    font-weight: 500;
    width: 100%;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.auth-section {
    background: rgba(255, 255, 255, 0.98); /* Nền trắng trong suốt */
    backdrop-filter: blur(8px);
    max-width: 420px;
    width: 90%;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    text-align: center;
    margin: 80px 0 60px 0; /* Đảm bảo không bị chèn bởi header/footer */
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.auth-section h2 {
    margin-bottom: 20px;
    color: #4a779e; /* Xanh nhạt dịu */
    font-size: 26px;
    font-weight: 500;
}

.auth-section label {
    display: block;
    text-align: left;
    font-weight: 500;
    margin: 12px 0 6px;
    color: #4a5568;
    font-size: 14px;
}

.auth-section input,
.auth-section select {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 12px;
    border: 1px solid #b0c4de; /* Viền đậm hơn để nổi bật */
    border-radius: 6px;
    box-sizing: border-box;
    font-size: 14px;
    background: #f0f4f8; /* Nền xám nhạt để tạo độ tương phản */
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.auth-section input:focus,
.auth-section select:focus {
    border-color: #4a779e;
    outline: none;
    box Hannah: box-shadow: 0 0 5px rgba(74, 119, 158, 0.3);
}

.auth-section button {
    background: linear-gradient(90deg, #68d391, #9ae6b4); /* Xanh lam nhạt */
    color: #fff;
    border: none;
    padding: 10px;
    border-radius: 6px;
    cursor: pointer;
    width: 100%;
    font-size: 15px;
    font-weight: 500;
    transition: background 0.3s ease, transform 0.2s ease;
}

.auth-section button:hover {
    background: linear-gradient(90deg, #5a9e72, #68d391);
    transform: scale(1.02);
}

.auth-section .forgot-btn {
    background: linear-gradient(90deg, #4a779e, #7aace3);
}

.auth-section .forgot-btn:hover {
    background: linear-gradient(90deg, #3c5f82, #4a779e);
    transform: scale(1.02);
}

.auth-section a {
    color: #4a779e;
    text-decoration: none;
    font-size: 13px;
    transition: color 0.3s ease;
}

.auth-section a:hover {
    color: #3c5f82;
    text-decoration: underline;
}

.hidden {
    display: none;
}

footer {
    position: fixed;
    bottom: 10px;
    width: 100%;
    text-align: center;
    color: #f7fafc;
    font-size: 12px;
    text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.5);
    z-index: 1000;
}

@media (max-height: 600px) {
    .auth-section {
        margin: 60px 0 40px 0;
        padding: 20px;
    }
    .header-container {
        padding: 10px 0;
        font-size: 20px;
    }
    footer {
        font-size: 11px;
    }
}
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <h1>Hệ Thống Bán Vé Máy Bay SEVEN AIRLINE</h1>
    </div>
</header>

<section class="auth-section">
    <div id="login-form-section">
        <h2>Đăng Nhập</h2>

        <?php if (isset($_SESSION['error_message'])): ?>
            <p style="color: #dc2626; text-align: center; margin-bottom: 20px; font-size: 14px;">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </p>
        <?php endif; ?>

        <form action="process_auth.php" method="POST" id="loginForm">
            <label for="role">Chọn vai trò</label>
            <select id="role" name="role" required onchange="handleRoleChange()">
                <option value="admin">Admin</option>
                <option value="staff">Nhân viên</option>
                <option value="customer">Khách hàng</option>
            </select>

            <div id="loginFields">
                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Đăng Nhập</button>

                <p id="forgot-password-link" style="margin-top: 20px;" class="hidden">
                    <a href="#" onclick="showForgotPassword(); return false;">Quên mật khẩu?</a>
                </p>
            </div>
        </form>
    </div>

    <div id="forgotPasswordSection" class="hidden">
        <h2>Đặt lại mật khẩu</h2>
        <label for="forgotUsername">Tên đăng nhập</label>
        <input type="text" id="forgotUsername" name="forgotUsername" oninput="checkUsername()">
        <span id="usernameStatus" style="font-size: 14px; display: block; margin: 10px 0;"></span>

        <div id="resetPasswordFields" class="hidden">
            <label for="newPassword">Mật khẩu mới</label>
            <input type="password" id="newPassword" name="newPassword">

            <label for="confirmNewPassword">Xác nhận mật khẩu mới</label>
            <input type="password" id="confirmNewPassword" name="confirmNewPassword">

            <button type="button" class="forgot-btn">Cập nhật mật khẩu</button>
        </div>
        <p style="margin-top: 20px;">
            <a href="#" onclick="backToLogin(); return false;">Quay lại đăng nhập</a>
        </p>
    </div>
</section>

<footer>
    © 2025 Hệ thống bán vé máy bay
</footer>

<script>
    function handleRoleChange() {
        let role = document.getElementById("role").value;
        let loginFields = document.getElementById("loginFields");
        let forgotPasswordLink = document.getElementById("forgot-password-link");

        if (role === "customer") {
            window.location.href = "customer_auth.php";
        } else {
            loginFields.style.display = "block";
            if (role === "staff") {
                forgotPasswordLink.classList.remove("hidden");
            } else {
                forgotPasswordLink.classList.add("hidden");
            }
        }
    }

    function showForgotPassword() {
        document.getElementById("login-form-section").classList.add("hidden");
        document.getElementById("forgotPasswordSection").classList.remove("hidden");
    }

    function backToLogin() {
        document.getElementById("forgotPasswordSection").classList.add("hidden");
        document.getElementById("login-form-section").classList.remove("hidden");
    }

    function checkUsername() {
        let username = document.getElementById("forgotUsername").value;
        let status = document.getElementById("usernameStatus");

        if (username.trim() === "") {
            status.innerHTML = "";
            document.getElementById("resetPasswordFields").classList.add("hidden");
            return;
        }

        fetch("check_staff.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "username=" + encodeURIComponent(username)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "valid") {
                status.innerHTML = "<span style='color: #16a34a;'>✔️ " + data.message + "</span>";
                document.getElementById("resetPasswordFields").classList.remove("hidden");
            } else {
                status.innerHTML = "<span style='color: #dc2626;'>❌ " + data.message + "</span>";
                document.getElementById("resetPasswordFields").classList.add("hidden");
            }
        })
        .catch(error => {
            status.innerHTML = "<span style='color: #dc2626;'>Lỗi kết nối server!</span>";
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

        fetch("reset_staff.php", {
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
</script>

</body>
</html>