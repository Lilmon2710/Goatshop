<?php
include('includes/db_connect.php');

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    // Kiểm tra định dạng tên đăng nhập (chỉ cho phép chữ, số và gạch dưới)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $message = "⚠️ Tên đăng nhập chỉ được chứa chữ, số và gạch dưới, không dấu và không khoảng trắng!";
    }
    // Kiểm tra độ dài mật khẩu
    elseif (strlen($password) < 6) {
        $message = "⚠️ Mật khẩu phải có ít nhất 6 ký tự!";
    } 
    // Kiểm tra xác nhận mật khẩu
    elseif ($password != $confirm) {
        $message = "⚠️ Mật khẩu xác nhận không khớp!";
    } 
    else {
        // Kiểm tra tên đăng nhập đã tồn tại chưa
        $check = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $message = "⚠️ Tên đăng nhập đã tồn tại, vui lòng chọn tên khác!";
        } else {
            // Mã hóa mật khẩu
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $role = 'customer';
            // Thêm tài khoản mới (mặc định role=user)
            $sql = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'customer')");
            $sql->bind_param("ss", $username, $hashed);

            if ($sql->execute()) {
                // Hiển thị thông báo rồi tự động chuyển hướng sau 3 giây
                echo "<script>alert('🎉 Đăng ký thành công! Hãy đăng nhập để tiếp tục.'); 
                window.location='index.php?page=login';</script>";
                exit;
            } else {
                $message = "⚠️ Lỗi khi tạo tài khoản: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Tạo tài khoản - GOATSHOP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    background-color: #fff8dc;
    font-family: Arial, sans-serif;
}
.container {
    max-width: 400px;
    margin-top: 100px;
    background: #fff;
    border: 2px solid #f5b400;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.btn-warning {
    background: #f5b400;
    color: #000;
    font-weight: 600;
}
.btn-warning:hover {
    background: #e0a800;
}
</style>
</head>
<body>

<div class="container text-center">
    <h3 class="mb-4">Tạo tài khoản mới</h3>
    <?php if ($message != "") echo "<div class='alert alert-danger'>$message</div>"; ?>

    <form method="POST">
        <div class="mb-3 text-start">
            <label class="form-label">Tên đăng nhập</label>
            <input type="text" name="username" class="form-control" required 
                   pattern="[A-Za-z0-9_]+" 
                   title="Chỉ nhập chữ, số và dấu gạch dưới, không dấu và không khoảng trắng.">
            <div class="form-text text-danger">Tên đăng nhập viết liền, không dấu và không khoảng trắng.</div>
        </div>

        <div class="mb-3 text-start">
            <label class="form-label">Mật khẩu</label>
            <input type="password" name="password" class="form-control" minlength="6" required>
            <div class="form-text text-danger">Mật khẩu phải có ít nhất 6 ký tự.</div>
        </div>

        <div class="mb-3 text-start">
            <label class="form-label">Xác nhận mật khẩu</label>
            <input type="password" name="confirm" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-warning w-100">Tạo tài khoản</button>
    </form>

    <p class="mt-3"><a href="index.php?page=login">← Quay lại đăng nhập</a></p>
</div>

</body>
</html>
