<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("includes/db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password']) || $password === $user['password']) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ];
            header("Location: index.php");
            exit;
        } else {
            $error = "Sai mật khẩu!";
        }
    } else {
        $error = "Tên đăng nhập không tồn tại!";
    }
}
?>


<!-- Giao diện đăng nhập -->
<div class="login-page" style="max-width:1000px; margin:40px auto; display:flex; gap:40px;">
    <!-- Cột bên trái: Đăng nhập -->
    <div class="login-left" style="flex:1; background:#fafafa; padding:30px; border-radius:10px;">
        <h3 style="margin-bottom:15px;">ĐĂNG NHẬP</h3>
        <p style="font-size:14px; color:#333; margin-bottom:20px;">
            Nếu bạn đã có tài khoản, xin vui lòng đăng nhập
        </p>

        <form action="login.php" method="POST">
            <div class="form-group" style="margin-bottom:15px;">
                <label for="username">Tên đăng nhập <span style="color:red">*</span></label>
                <input type="text" id="username" name="username" required
                    style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
            </div>

            <div class="form-group" style="margin-bottom:15px;">
                <label for="password">Mật khẩu <span style="color:red">*</span></label>
                <input type="password" id="password" name="password" required
                    style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
            </div>

            <?php if (!empty($error)): ?>
                <p style="color:red; font-size:13px;"><?php echo $error; ?></p>
            <?php endif; ?>

            <button type="submit"
                style="background:#f5b400; color:#000; border:none; padding:10px 20px; font-weight:600; border-radius:5px; cursor:pointer;">
                <i class="fa fa-lock"></i> Đăng nhập
            </button>

            <a href="#" style="margin-left:15px; font-size:14px; color:#555;">Quên mật khẩu?</a>
        </form>
    </div>

    <!-- Cột bên phải: Khách hàng mới -->
    <div class="login-right" style="flex:1; background:#fafafa; padding:30px; border-radius:10px;">
        <h3 style="margin-bottom:15px;">KHÁCH HÀNG MỚI</h3>
        <p style="font-size:14px; line-height:1.6; color:#333;">
            Bằng cách tạo một tài khoản với cửa hàng của chúng tôi, bạn sẽ có thể thực hiện
            những quy trình mua hàng nhanh hơn, lưu trữ nhiều địa chỉ gửi hàng, xem và theo
            dõi đơn đặt hàng của bạn trong tài khoản của bạn và nhiều hơn nữa.
        </p>

        <a href="register.php" 
           class="btn btn-warning" 
           style="display:inline-block; margin-top:20px; background:#f5b400; color:#000; font-weight:600; padding:10px 20px; border-radius:5px; text-decoration:none;">
           <i class="bi bi-person-plus"></i> Tạo tài khoản
        </a>
    </div>
</div>
